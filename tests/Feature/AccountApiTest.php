<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountApiTest extends TestCase
{
  use RefreshDatabase;
  // public function test_api_get_account()
  // {
  //   $this->withoutMiddleware(\Illuminate\Auth\Middleware\Authenticate::class);

  //     $acc = \App\Models\Account::factory()->create();

  //     $response = $this->get("/api/account/{$acc->id}/tree");

  //     $response->assertStatus(200);
  // }

  public function test_api_get_account()
  {
    $this->actingAs(\App\Models\User::factory()->create());

    $acc = \App\Models\Account::factory()->create([
      'number' => 'AC555',
      'status_id' => 1
    ]);

    // لازم نستخدم $acc->id مش $acc->number
    $response = $this->get("/api/account/{$acc->id}/tree");

    $response->assertStatus(200);
  }
}
