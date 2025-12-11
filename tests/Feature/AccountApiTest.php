<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase; 

class AccountApiTest extends TestCase
{
  use RefreshDatabase;
    public function test_api_get_account()
    {
        $acc = \App\Models\Account::factory()->create();

        $response = $this->get("/api/accounts/{$acc->id}");

        $response->assertStatus(200);
    }
}