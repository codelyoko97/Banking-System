<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\StaffService;
use Illuminate\Auth\Access\Response as AuthResponse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;
use Mockery;
use PHPUnit\Framework\Attributes\Test;

class StaffControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $staffService;

    public function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();

        // إنشاء مستخدم تجريبي وتسجيل دخوله
        $user = User::factory()->create();
        $this->actingAs($user);

        // تجاوز الـ authorize
        Gate::shouldReceive('authorize')->andReturn(AuthResponse::allow());
        Gate::shouldReceive('allows')->andReturn(true);
        Gate::shouldReceive('forUser')->andReturnSelf();
        Gate::shouldReceive('check')->andReturn(true);

        // Mock StaffService
        $this->staffService = Mockery::mock(StaffService::class);
        $this->app->instance(StaffService::class, $this->staffService);

        // تنظيف جدول roles وإضافة بيانات تجريبية
        DB::table('roles')->truncate();
        DB::table('roles')->insert(['id' => 1, 'name' => 'manager']);
        DB::table('roles')->insert(['id' => 2, 'name' => 'employee']);
        DB::table('roles')->insert(['id' => 3, 'name' => 'supervisor']);
    }

    #[Test]
    public function test_index_returns_staff_list()
    {
        $staffList = [
            ['id' => 1, 'name' => 'John Doe', 'email' => 'john@example.com', 'phone' => '123456789', 'role_id' => 1],
            ['id' => 2, 'name' => 'Jane Smith', 'email' => 'jane@example.com', 'phone' => '987654321', 'role_id' => 2],
        ];

        $this->staffService->shouldReceive('listStaff')
            ->once()
            ->andReturn($staffList);

        $response = $this->getJson('/api/admin/staff');

        $response->assertStatus(200)
                 ->assertJson($staffList);
    }

    #[Test]
    public function test_store_creates_staff()
    {
        $payload = [
            'name' => 'Ali',
            'email' => 'ali@example.com',
            'password' => 'Secret123!',
            'phone' => '+31612345678',
            'role_id' => 2,
        ];

        $created = [
            'id' => 10,
            'name' => 'Ali',
            'email' => 'ali@example.com',
            'phone' => '+31612345678',
            'role_id' => 2,
            'is_verified' => true,
            'created_at' => now()->toISOString(),
            'updated_at' => now()->toISOString(),
        ];

        $this->staffService->shouldReceive('createStaff')
            ->once()
            ->andReturn($created);

        $response = $this->postJson('/api/admin/createEmployee', $payload);

        $response->assertStatus(201)
                 ->assertJsonFragment([
                     'name' => 'Ali',
                     'email' => 'ali@example.com',
                     'role_id' => 2,
                 ]);
    }

    #[Test]
    public function test_update_role_changes_staff_role()
    {
        $payload = ['role_id' => 3];
        $updated = [
            'id' => 12,
            'name' => 'asdasd',
            'email' => 'sadasqwe@gmail.com',
            'role_id' => 3,
            'phone' => '123123121',
            'is_verified' => true,
            'created_at' => now()->toISOString(),
            'updated_at' => now()->toISOString(),
        ];

        $this->staffService->shouldReceive('updateRole')
            ->with(12, 3)
            ->once()
            ->andReturn($updated);

        $response = $this->postJson('/api/admin/staff/12/role', $payload);

        $response->assertStatus(200)
                 ->assertJsonFragment([
                     'id' => 12,
                     'role_id' => 3,
                 ]);
    }

    #[Test]
    public function test_employees_returns_all_employees()
    {
        $employees = [
            ['id' => 1, 'name' => 'John Doe', 'email' => 'john@example.com'],
            ['id' => 2, 'name' => 'Jane Smith', 'email' => 'jane@example.com'],
        ];

        $this->staffService->shouldReceive('getAllEmployees')
            ->once()
            ->andReturn($employees);

        $response = $this->getJson('/api/admin/getEmployees');

        $response->assertStatus(200)
                 ->assertJson($employees);
    }

    #[Test]
    public function test_destroy_removes_staff()
    {
        $this->staffService->shouldReceive('deleteStaff')
            ->with(1)
            ->once();

        $response = $this->deleteJson('/api/admin/removeuser/1');

        $response->assertStatus(200)
                 ->assertJson(['message' => 'staff removed']);
    }

    #[Test]
    public function test_get_account_user_returns_user()
    {
        $user = ['id' => 10, 'name' => 'Customer X', 'account_number' => '12345'];

        $this->staffService->shouldReceive('getAccountUser')
            ->with('12345')
            ->once()
            ->andReturn($user);

        $response = $this->postJson('/api/admin/getAccountUser', [
            'account_number' => '12345',
        ]);

        $response->assertStatus(200)
                 ->assertJson($user);
    }

    #[Test]
    public function test_get_account_user_validation_error()
    {
        $response = $this->postJson('/api/admin/getAccountUser', []);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['account_number']);
    }
}