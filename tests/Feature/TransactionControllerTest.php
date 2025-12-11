<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class TransactionControllerTest extends TestCase
{
  use RefreshDatabase;

  /** ✅ اختبار تنفيذ معاملة جديدة */
  public function test_transaction_process_successfully()
  {
    $this->withoutMiddleware();
    $user = User::factory()->create();
    $this->actingAs($user, 'sanctum');
    $account = Account::factory()->create([
      'customer_id' => $user->id
    ]);
    $payload = [
      'account_id' => $account->number,
      'amount' => 100,
      'type' => 'deposit',
      'account_related_id' => null,
      'description' => 'Test deposit',
    ];

    $response = $this->postJson('/api/transaction', $payload);
    $response->assertStatus(200)
      ->assertJsonStructure(['id', 'type', 'amount', 'status']);
  }

  /** ✅ اختبار الموافقة على معاملة */
  public function test_approve_transaction_successfully()
  {
    $this->withoutMiddleware();
    $user = User::factory()->create();
    $this->actingAs($user, 'sanctum');

    $transaction = Transaction::factory()->create(['status' => 'pending']);

    $response = $this->postJson("/api/transaction/{$transaction->id}/approve");

    $response->assertStatus(200)
      ->assertJsonFragment(['message' => 'Transaction approved successfully'])
      ->assertJsonPath('transaction.status', 'completed');
  }

  /** ✅ اختبار رفض معاملة */
  public function test_reject_transaction_successfully()
  {
    $this->withoutMiddleware();
    $user = User::factory()->create();
    $this->actingAs($user, 'sanctum');

    $transaction = Transaction::factory()->create(['status' => 'pending']);

    $response = $this->postJson("/api/transaction/{$transaction->id}/reject");

    $response->assertStatus(200)
      ->assertJsonFragment(['message' => 'Transaction rejected successfully']);
  }

  /** ✅ اختبار إنشاء معاملة مجدولة */
  public function test_store_scheduled_transaction_successfully()
  {
    $this->withoutMiddleware();
    $user = User::factory()->create();
    $user2 = User::factory()->create();
    $this->actingAs($user, 'sanctum');

    $account = Account::factory()->create([
      'customer_id' => $user->id
    ]);
    $related = Account::factory()->create([
      'customer_id' => $user2->id
    ]);
    $payload = [
      'account_id' => $account->number,
      'amount' => 200,
      'type' => 'transfer',
      'account_related_id' => $related->number,
      'description' => 'Scheduled transfer',
      'schedule_date' => now()->addDay()->toDateString(),
      'frequency' => 'daily',
      'next_run' => now()->addDay()->toDateString(),
    ];

    $response = $this->postJson('/api/scheduled-transactions', $payload);
    
    $response->assertStatus(201)
    ->assertJsonFragment(['message' => 'Scheduled transaction created successfully']);
  }
  
  /** ✅ اختبار عرض معاملات المستخدم */
  public function test_show_transactions_endpoint()
  {
    $this->withoutMiddleware();
    $user = User::factory()->create();
    $this->actingAs($user, 'sanctum');
    
    $account = Account::factory()->create();
    Transaction::factory()->count(2)->create([
      'account_id' => $account->id,
      'type' => 'deposit',
      'status' => 'completed',
      'amount' => 100,
    ]);
    
    $response = $this->getJson('/api/show-transactions');
    
    $response->assertStatus(200)
      ->assertJsonStructure([
        '*' => ['id', 'account_id', 'status', 'amount', 'type', 'description']
      ]);
  }

  /** ✅ اختبار عرض جميع المعاملات */
  public function test_all_transactions_endpoint()
  {
    $this->withoutMiddleware();
    $user = User::factory()->create();
    $this->actingAs($user, 'sanctum');
    $account = Account::factory()->create();

    Transaction::factory()->count(3)->create([
      'account_id' => $account->id,
      'type' => 'deposit',
      'status' => 'completed',
      'amount' => 50,
    ]);

    $response = $this->getJson('/api/transactions/all');

    $response->assertStatus(200)
      ->assertJsonStructure([['id', 'type', 'amount', 'status']]);
  }
}
