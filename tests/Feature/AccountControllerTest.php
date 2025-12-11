<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Account;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountControllerTest extends TestCase
{
  use RefreshDatabase;

  public function test_store_account_successfully()
  {
    $this->withoutMiddleware();
    $user = User::factory()->create();

    $this->actingAs($user, 'sanctum');

    $payload = [
      'customer_id' => $user->id,
      'type_id' => 1,
      'balance' => 1000,
      'account_related_id' => null,
    ];

    $response = $this->postJson('/api/account/', $payload);

    $response->assertStatus(201)
      ->assertJsonFragment([
        'customer_id' => $user->id,
        'balance' => '1000.0000', // balance يرجع كسلسلة نصية
      ]);
  }

  public function test_update_account_successfully()
  {
    $this->withoutMiddleware();
    $user = User::factory()->create();
    $this->actingAs($user, 'sanctum');

    $account = Account::factory()->create([
      'balance' => 500,
      'account_related_id' => null,
    ]);

    // أنشئ حساب ثاني فعليًا ليكون هو الـ related account
    $related = Account::factory()->create();

    $payload = ['account_related_id' => $related->id];

    $response = $this->postJson("/api/account/{$account->id}", $payload);

    $response->assertStatus(200);
    $this->assertEquals($related->id, $response->json('account_related_id'));
  }

  public function test_close_account_successfully()
  {
    $this->withoutMiddleware();
    $user = User::factory()->create();
    $this->actingAs($user, 'sanctum');

    $account = Account::factory()->create([
      'balance' => 0,
      'status_id' => 1, // بدل status => 'open'
    ]);

    $response = $this->getJson("/api/account/{$account->id}/close");

    $response->assertStatus(200)
      ->assertJsonFragment(['message' => 'closed']);
  }

  public function test_full_balance_endpoint()
  {
    $this->withoutMiddleware();
    $user = User::factory()->create();
    $this->actingAs($user, 'sanctum');

    $account = Account::factory()->create(['balance' => 100]);

    $response = $this->getJson("/api/account/{$account->id}/full-balance");

    $response->assertStatus(200)
      ->assertJsonStructure(['balance']);
  }

  public function test_tree_endpoint()
  {
    $this->withoutMiddleware();
    $user = User::factory()->create();
    $this->actingAs($user, 'sanctum');

    $account = Account::factory()->create();

    $response = $this->getJson("/api/account/{$account->id}/tree");

    $response->assertStatus(200);
  }

  // public function test_index_filters_accounts_by_status()
  // {
  //     $this->withoutMiddleware();
  //     $user = User::factory()->create();
  //     $this->actingAs($user, 'sanctum');

  //     Account::factory()->create(['status_id' => 1]); // open
  //     Account::factory()->create(['status_id' => 2]); // closed

  //     $response = $this->getJson('/api/account/all?status=open');

  //     $response->assertStatus(200);
  //     // تأكد أن الاستجابة فيها بيانات
  //     $this->assertGreaterThan(0, count($response->json()));
  // }


public function test_index_filters_accounts_by_status()
{
    $this->withoutMiddleware();
    $user = User::factory()->create();
    $this->actingAs($user, 'sanctum');

    // أنشئ حسابات باستخدام status_id
    $accountActive = Account::factory()->create(['status_id' => 1]); // active
    $accountClosed = Account::factory()->create(['status_id' => 4]); // closed

    // فلترة بالحالة النصية
    $response = $this->getJson('/api/account/all?status=active');

    $response->assertStatus(200);

    $accounts = $response->json();

    $this->assertTrue(
        collect($accounts)->contains(fn($acc) => $acc['id'] === $accountActive->id),
        'Active account ID not found in filtered result'
    );
}


  public function test_change_status_successfully()
  {
    $this->withoutMiddleware();
    $user = User::factory()->create();
    $this->actingAs($user, 'sanctum');

    $account = Account::factory()->create(['status_id' => 1]);

    $response = $this->postJson("/api/account/{$account->id}/status", [
      'status' => 'closed',
    ]);

    $response->assertStatus(200)
      ->assertJsonFragment(['message' => 'Status updated successfully']);

    // تأكد أن الحالة تغيرت عن القيمة الأصلية
    $this->assertNotEquals(1, $response->json('account.status_id'));
  }
}
