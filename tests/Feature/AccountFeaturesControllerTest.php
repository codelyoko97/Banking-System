<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Account;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AccountFeaturesControllerTest extends TestCase
{
  use RefreshDatabase;

  public function test_index_returns_features()
  {
    $this->withoutMiddleware();
    $user = User::factory()->create();
    $this->actingAs($user, 'sanctum');

    $account = Account::factory()->create();

    DB::table('account_features')->insert([
      'account_id' => $account->id,
      'feature'    => 'overdraft',
      'created_at' => now(),
      'updated_at' => now(),
    ]);

    $response = $this->getJson("/api/accounts/{$account->id}/features");

    $response->assertStatus(200)
      ->assertJsonPath('account_id', (string) $account->id);

    $this->assertContains('overdraft', $response->json('features'));
  }

public function test_store_adds_new_feature()
{
    $this->withoutMiddleware();
    $user = User::factory()->create();
    $this->actingAs($user, 'sanctum');

    $account = Account::factory()->create();

    DB::table('account_features')->where('account_id', $account->id)->delete();

    $response = $this->postJson("/api/accounts/{$account->id}/features", [
        'feature' => 'overdraft',
    ]);

    $response->assertStatus(200)
             ->assertJsonFragment(['message' => 'Feature added']);

    $this->assertDatabaseHas('account_features', [
        'account_id' => $account->id,
        'feature'    => 'overdraft',
    ]);
}



  public function test_store_prevents_duplicate_feature()
  {
    $this->withoutMiddleware();
    $user = User::factory()->create();
    $this->actingAs($user, 'sanctum');

    $account = Account::factory()->create();

    // أضف ميزة مسبقاً
    DB::table('account_features')->insert([
      'account_id' => $account->id,
      'feature'    => 'premium',
      'created_at' => now(),
      'updated_at' => now(),
    ]);

    $response = $this->postJson("/api/accounts/{$account->id}/features", [
      'feature' => 'premium',
    ]);

    $response->assertStatus(409)
      ->assertJsonFragment(['message' => 'Feature already exists']);
  }

  public function test_store_rejects_invalid_feature()
  {
    $this->withoutMiddleware();
    $user = User::factory()->create();
    $this->actingAs($user, 'sanctum');

    $account = Account::factory()->create();

    $response = $this->postJson("/api/accounts/{$account->id}/features", [
      'feature' => 'invalid-feature',
    ]);

    $response->assertStatus(422);
  }

  public function test_destroy_removes_feature()
  {
    $this->withoutMiddleware();
    $user = User::factory()->create();
    $this->actingAs($user, 'sanctum');

    $account = Account::factory()->create();

    DB::table('account_features')->insert([
      'account_id' => $account->id,
      'feature'    => 'overdraft',
      'created_at' => now(),
      'updated_at' => now(),
    ]);

    $response = $this->deleteJson("/api/accounts/{$account->id}/features/overdraft");

    $response->assertStatus(200)
      ->assertJsonFragment(['message' => 'Feature removed']);

    $this->assertDatabaseMissing('account_features', [
      'account_id' => $account->id,
      'feature'    => 'overdraft',
    ]);
  }
}
