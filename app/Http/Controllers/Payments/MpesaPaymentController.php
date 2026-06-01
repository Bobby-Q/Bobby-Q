<?php

namespace App\Http\Controllers\Payments;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Payment;
use App\Models\PaymentRequest;
use App\Services\Payments\MpesaDarajaClient;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class MpesaPaymentController extends Controller
{
    public function create(): View
    {
        return view('payments.mpesa.create');
    }

    public function store(Request $request, MpesaDarajaClient $mpesa): RedirectResponse
    {
        $validated = $request->validate([
            'phone_number' => ['required', 'string', 'max:32'],
            'amount' => ['required', 'numeric', 'min:1', 'max:500000'],
            'account_reference' => ['required', 'string', 'max:64'],
            'description' => ['nullable', 'string', 'max:120'],
            'loan_id' => ['nullable', 'integer', 'exists:loans,id'],
            'borrower_id' => ['nullable', 'integer', 'exists:borrowers,id'],
        ]);

        $description = $validated['description'] ?? 'Loan Suite payment';

        try {
            $phoneNumber = $mpesa->normalizePhoneNumber($validated['phone_number']);
        } catch (Throwable $exception) {
            throw ValidationException::withMessages([
                'phone_number' => $exception->getMessage(),
            ]);
        }

        $paymentRequest = PaymentRequest::create([
            'loan_id' => $validated['loan_id'] ?? null,
            'borrower_id' => $validated['borrower_id'] ?? null,
            'requested_by' => $request->user()->id,
            'provider' => 'mpesa',
            'method' => 'stk_push',
            'phone_number' => $phoneNumber,
            'amount' => $validated['amount'],
            'account_reference' => $validated['account_reference'],
            'description' => $description,
            'status' => 'pending',
            'raw_request' => Arr::except($validated, ['phone_number']),
        ]);

        try {
            $response = $mpesa->initiateStkPush(
                $phoneNumber,
                (float) $validated['amount'],
                $validated['account_reference'],
                $description,
            );
        } catch (RequestException $exception) {
            $paymentRequest->update([
                'status' => 'failed',
                'result_description' => $exception->response?->body() ?: $exception->getMessage(),
            ]);

            throw ValidationException::withMessages([
                'phone_number' => 'M-Pesa request failed. Please verify Daraja credentials and try again.',
            ]);
        } catch (Throwable $exception) {
            $paymentRequest->update([
                'status' => 'failed',
                'result_description' => $exception->getMessage(),
            ]);

            throw ValidationException::withMessages([
                'phone_number' => $exception->getMessage(),
            ]);
        }

        $paymentRequest->update([
            'merchant_request_id' => $response['MerchantRequestID'] ?? null,
            'checkout_request_id' => $response['CheckoutRequestID'] ?? null,
            'status' => (($response['ResponseCode'] ?? null) === '0') ? 'requested' : 'failed',
            'result_code' => $response['ResponseCode'] ?? null,
            'result_description' => $response['ResponseDescription'] ?? $response['errorMessage'] ?? null,
            'raw_response' => $response,
        ]);

        AuditLog::create([
            'user_id' => $request->user()->id,
            'event' => 'mpesa.stk_push.requested',
            'auditable_type' => PaymentRequest::class,
            'auditable_id' => $paymentRequest->id,
            'ip_address' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 512, ''),
            'new_values' => [
                'amount' => $paymentRequest->amount,
                'phone_number' => $paymentRequest->phone_number,
                'status' => $paymentRequest->status,
            ],
        ]);

        return redirect()
            ->route('payments.mpesa.create')
            ->with('status', 'M-Pesa STK push sent. Confirm the payment on the customer phone.');
    }

    public function callback(Request $request): JsonResponse
    {
        $callback = $request->input('Body.stkCallback', []);
        $checkoutRequestId = $callback['CheckoutRequestID'] ?? null;

        if (! $checkoutRequestId) {
            return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
        }

        $paymentRequest = PaymentRequest::where('checkout_request_id', $checkoutRequestId)->first();

        if (! $paymentRequest) {
            AuditLog::create([
                'event' => 'mpesa.stk_push.callback.unmatched',
                'auditable_type' => PaymentRequest::class,
                'ip_address' => $request->ip(),
                'user_agent' => Str::limit((string) $request->userAgent(), 512, ''),
                'new_values' => $request->all(),
            ]);

            return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
        }

        $resultCode = (string) ($callback['ResultCode'] ?? '');
        $metadata = collect($callback['CallbackMetadata']['Item'] ?? [])->pluck('Value', 'Name');
        $receipt = $metadata->get('MpesaReceiptNumber');
        $paidAt = $this->parseTransactionDate($metadata->get('TransactionDate'));

        $paymentRequest->update([
            'status' => $resultCode === '0' ? 'paid' : 'failed',
            'result_code' => $resultCode,
            'result_description' => $callback['ResultDesc'] ?? null,
            'raw_callback' => $request->all(),
            'paid_at' => $resultCode === '0' ? ($paidAt ?? now()) : null,
        ]);

        if ($resultCode === '0' && $paymentRequest->borrower_id && $receipt) {
            Payment::firstOrCreate(
                ['reference' => $receipt],
                [
                    'loan_id' => $paymentRequest->loan_id,
                    'borrower_id' => $paymentRequest->borrower_id,
                    'received_by' => $paymentRequest->requested_by,
                    'amount' => $metadata->get('Amount', $paymentRequest->amount),
                    'method' => 'mpesa',
                    'paid_at' => $paidAt ?? now(),
                    'status' => 'posted',
                    'metadata' => [
                        'phone_number' => $metadata->get('PhoneNumber'),
                        'checkout_request_id' => $paymentRequest->checkout_request_id,
                        'merchant_request_id' => $paymentRequest->merchant_request_id,
                    ],
                ],
            );
        }

        AuditLog::create([
            'event' => 'mpesa.stk_push.callback.received',
            'auditable_type' => PaymentRequest::class,
            'auditable_id' => $paymentRequest->id,
            'ip_address' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 512, ''),
            'new_values' => [
                'status' => $paymentRequest->status,
                'result_code' => $resultCode,
                'receipt' => $receipt,
            ],
        ]);

        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
    }

    private function parseTransactionDate(mixed $transactionDate): ?Carbon
    {
        if (! is_numeric($transactionDate)) {
            return null;
        }

        return Carbon::createFromFormat('YmdHis', (string) $transactionDate, 'Africa/Nairobi');
    }
}
