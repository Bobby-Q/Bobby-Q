<?php

namespace Tests\Feature;

use App\Models\PaymentRequest;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MpesaPaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_users_can_initiate_mpesa_stk_push(): void
    {
        Http::fake([
            'https://sandbox.safaricom.co.ke/oauth/v1/generate*' => Http::response(['access_token' => 'test-token'], 200),
            'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest' => Http::response([
                'MerchantRequestID' => 'merchant-123',
                'CheckoutRequestID' => 'checkout-123',
                'ResponseCode' => '0',
                'ResponseDescription' => 'Success. Request accepted for processing',
            ], 200),
        ]);

        config()->set('services.mpesa.consumer_key', 'consumer-key');
        config()->set('services.mpesa.consumer_secret', 'consumer-secret');
        config()->set('services.mpesa.shortcode', '174379');
        config()->set('services.mpesa.passkey', 'passkey');
        config()->set('services.mpesa.callback_url', 'https://example.com/mpesa/stk/callback');

        $user = User::factory()->create();
        $this->grant($user, 'payments.manage');

        $response = $this->actingAs($user)->post('/payments/mpesa', [
            'phone_number' => '0712345678',
            'amount' => 150,
            'account_reference' => 'LN-1001',
            'description' => 'Loan repayment',
        ]);

        $response->assertRedirect(route('payments.mpesa.create'));
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('payment_requests', [
            'provider' => 'mpesa',
            'method' => 'stk_push',
            'phone_number' => '254712345678',
            'amount' => '150.00',
            'account_reference' => 'LN-1001',
            'merchant_request_id' => 'merchant-123',
            'checkout_request_id' => 'checkout-123',
            'status' => 'requested',
        ]);

        Http::assertSentCount(2);
    }

    public function test_mpesa_callback_updates_payment_request_status(): void
    {
        PaymentRequest::create([
            'provider' => 'mpesa',
            'method' => 'stk_push',
            'phone_number' => '254712345678',
            'amount' => 150,
            'account_reference' => 'LN-1001',
            'checkout_request_id' => 'checkout-123',
            'status' => 'requested',
        ]);

        $response = $this->postJson('/mpesa/stk/callback', [
            'Body' => [
                'stkCallback' => [
                    'MerchantRequestID' => 'merchant-123',
                    'CheckoutRequestID' => 'checkout-123',
                    'ResultCode' => 0,
                    'ResultDesc' => 'The service request is processed successfully.',
                    'CallbackMetadata' => [
                        'Item' => [
                            ['Name' => 'Amount', 'Value' => 150],
                            ['Name' => 'MpesaReceiptNumber', 'Value' => 'RKT123456'],
                            ['Name' => 'TransactionDate', 'Value' => 20260601235959],
                            ['Name' => 'PhoneNumber', 'Value' => 254712345678],
                        ],
                    ],
                ],
            ],
        ]);

        $response->assertOk()->assertJson(['ResultCode' => 0]);

        $this->assertDatabaseHas('payment_requests', [
            'checkout_request_id' => 'checkout-123',
            'status' => 'paid',
            'result_code' => '0',
        ]);
    }

    public function test_mpesa_payment_page_requires_authentication(): void
    {
        $this->get('/payments/mpesa')->assertRedirect('/login');
    }

    private function grant(User $user, string $permission): void
    {
        $role = Role::create([
            'name' => 'test-'.str_replace('.', '-', $permission),
            'display_name' => 'Test role',
        ]);
        $perm = Permission::create([
            'key' => $permission,
            'module' => 'Testing',
            'name' => $permission,
        ]);
        $role->permissions()->attach($perm);
        $user->roles()->attach($role);
    }
}
