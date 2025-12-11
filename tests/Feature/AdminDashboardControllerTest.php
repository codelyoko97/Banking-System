<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardControllerTest extends TestCase
{
  use RefreshDatabase;

  protected User $user;

  public function setUp(): void
  {
    parent::setUp();
    $this->withoutMiddleware();
    $this->user = User::factory()->create(['role_id' => 1]); // admin
    $this->actingAs($this->user, 'sanctum');
  }

  /** @test */
  public function test_transactions_weekly()
  {
    $response = $this->getJson('/api/admin/charts/transactions-weekly');
    $response->assertStatus(200);
  }

  /** @test */
  public function test_transactions_status()
  {
    $response = $this->getJson('/api/admin/charts/transactions-status');
    $response->assertStatus(200);
  }

  /** @test */
  public function test_accounts_monthly()
  {
    $response = $this->getJson('/api/admin/charts/accounts-monthly?days=15');
    $response->assertStatus(200);
  }

  /** @test */
  public function test_top_customers()
  {
    $response = $this->getJson('/api/admin/top/customers?limit=5');
    $response->assertStatus(200);
  }

  /** @test */
  public function test_accounts_today()
  {
    $response = $this->getJson('/api/admin/stats/accounts-today');
    $response->assertStatus(200);
  }

  /** @test */
  public function test_transactions_24h()
  {
    $response = $this->getJson('/api/admin/stats/transactions-24h');
    $response->assertStatus(200);
  }

  /** @test */
  public function test_customers_list()
  {
    $response = $this->getJson('/api/admin/users/customers');
    $response->assertStatus(200);
  }

  /** @test */
  public function test_employees_list()
  {
    $response = $this->getJson('/api/admin/users/employees');
    $response->assertStatus(200);
  }

  /** @test */
  public function test_logs()
  {
    $response = $this->getJson('/api/admin/logs?per_page=5');
    $response->assertStatus(200);
  }

  /** @test */
  public function test_logs_export()
  {
    $response = $this->get('/api/admin/logs/export');
    $response->assertStatus(200);

    $this->assertStringStartsWith('text/csv', $response->headers->get('Content-Type'));
  }

  /** @test */
  public function test_add_manager_success()
  {
    $payload = [
      'name'     => 'Manager Test',
      'email'    => 'manager@test.com',
      'phone'    => '123456789',
      'password' => 'password123',
    ];

    $response = $this->postJson('/api/admin/addManager', $payload);

    $response->assertStatus(200)
      ->assertJsonFragment(['message' => 'Manager created successfully']);
  }

  /** @test */
  public function test_add_manager_validation_error()
  {
    $payload = [
      'name'     => '',
      'email'    => 'not-an-email',
      'phone'    => '',
      'password' => '123',
    ];

    $response = $this->postJson('/api/admin/addManager', $payload);

    $response->assertStatus(422)
      ->assertJsonStructure(['message', 'errors']);
  }
}
