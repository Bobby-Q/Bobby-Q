<?php

namespace App\Services\Payments;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;

class MpesaDarajaClient
{
    public function initiateStkPush(string $phoneNumber, float $amount, string $accountReference, string $description): array
    {
        $timestamp = now('Africa/Nairobi')->format('YmdHis');
        $shortcode = $this->requiredConfig('shortcode');
        $passkey = $this->requiredConfig('passkey');

        $payload = [
            'BusinessShortCode' => $shortcode,
            'Password' => base64_encode($shortcode.$passkey.$timestamp),
            'Timestamp' => $timestamp,
            'TransactionType' => config('services.mpesa.transaction_type', 'CustomerPayBillOnline'),
            'Amount' => (int) ceil($amount),
            'PartyA' => $this->normalizePhoneNumber($phoneNumber),
            'PartyB' => $shortcode,
            'PhoneNumber' => $this->normalizePhoneNumber($phoneNumber),
            'CallBackURL' => config('services.mpesa.callback_url') ?: url('/mpesa/stk/callback'),
            'AccountReference' => $accountReference,
            'TransactionDesc' => $description,
        ];

        return $this->http()
            ->withToken($this->accessToken())
            ->post($this->baseUrl().'/mpesa/stkpush/v1/processrequest', $payload)
            ->throw()
            ->json();
    }

    public function accessToken(): string
    {
        $response = Http::withBasicAuth(
            $this->requiredConfig('consumer_key'),
            $this->requiredConfig('consumer_secret'),
        )
            ->acceptJson()
            ->get($this->baseUrl().'/oauth/v1/generate', ['grant_type' => 'client_credentials'])
            ->throw()
            ->json();

        if (empty($response['access_token'])) {
            throw new InvalidArgumentException('M-Pesa access token response did not include an access_token.');
        }

        return $response['access_token'];
    }

    public function normalizePhoneNumber(string $phoneNumber): string
    {
        $digits = preg_replace('/\D+/', '', $phoneNumber) ?? '';

        if (str_starts_with($digits, '0')) {
            $digits = '254'.substr($digits, 1);
        }

        if (str_starts_with($digits, '7') || str_starts_with($digits, '1')) {
            $digits = '254'.$digits;
        }

        if (! preg_match('/^254(7|1)\d{8}$/', $digits)) {
            throw new InvalidArgumentException('Use a valid Kenyan Safaricom phone number in 2547XXXXXXXX or 07XXXXXXXX format.');
        }

        return $digits;
    }

    private function http(): PendingRequest
    {
        return Http::acceptJson()
            ->asJson()
            ->timeout((int) config('services.mpesa.timeout', 30))
            ->retry(2, 250, throw: false);
    }

    private function baseUrl(): string
    {
        return rtrim((string) config('services.mpesa.base_url'), '/');
    }

    private function requiredConfig(string $key): string
    {
        $value = config("services.mpesa.{$key}");

        if (! is_string($value) || trim($value) === '') {
            throw new InvalidArgumentException("Missing M-Pesa Daraja configuration value: services.mpesa.{$key}.");
        }

        return trim($value);
    }
}
