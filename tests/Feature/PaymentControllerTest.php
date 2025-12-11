<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class PaymentControllerTest extends TestCase
{
  use RefreshDatabase;

  /** ✅ اختبار الدفع عبر Stripe */
  public function test_process_payment_with_stripe()
  {
    $this->withoutMiddleware();
    $user = User::factory()->create();
    $this->actingAs($user, 'sanctum');
    $account = Account::factory()->create([
      'customer_id' => $user->id
    ]);
    $payload = [
      'driver' => 'stripe',
      'stripeToken' => 'tok_visa',
      'amount' => 100,
      'account_id' => $account->number,
    ];

    $response = $this->postJson('/api/pay', $payload);

    $response->assertStatus(200)
      ->assertJsonStructure(['success', 'transactionId', 'amount']);
  }

  /** ✅ اختبار الدفع عبر Braintree */
  public function test_process_payment_with_braintree()
  {
    $this->withoutMiddleware();
    $user = User::factory()->create();
    $this->actingAs($user, 'sanctum');
    $account = Account::factory()->create([
      'customer_id' => $user->id
    ]);
    $payload = [
      'driver' => 'braintree',
      'nonce' => 'fake-valid-nonce',
      'amount' => 150,
      'account_id' => $account->number,
    ];

    $response = $this->postJson('/api/pay', $payload);

    $response->assertStatus(200)
      ->assertJsonStructure(['success', 'transactionId', 'amount']);
  }

  /** ✅ اختبار السحب عبر Stripe */
  // public function test_process_withdraw_with_stripe()
  // {
  //   $this->withoutMiddleware();
  //   $user = User::factory()->create();
  //   $this->actingAs($user, 'sanctum');

  //   $payload = [
  //     'driver' => 'stripe',
  //     'amount' => 100,
  //   ];

  //   $response = $this->postJson('/api/withdraw', $payload);

  //   $response->assertStatus(200)
  //     ->assertJsonStructure(['success', 'transactionId', 'amount']);
  // }

  /** ✅ اختبار جلب الرصيد */
  public function test_get_balance_with_stripe()
  {
    $this->withoutMiddleware();
    $user = User::factory()->create();
    $this->actingAs($user, 'sanctum');

    $payload = [
      'driver' => 'stripe',
    ];

    $response = $this->postJson('/api/balance', $payload);

    $response->assertStatus(200)
      ->assertJsonStructure(['success', 'balance']);
  }
}
