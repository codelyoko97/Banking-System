<?php

namespace Tests\Feature;

use App\Services\SystemHealthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Mockery;

class AdminHealthControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_health_returns_successful_response()
    {
        $this->withoutMiddleware();

        // نعمل mock للخدمة بحيث ترجع بيانات صحية كاملة
        $mock = Mockery::mock(SystemHealthService::class);
        $mock->shouldReceive('get')->once()->andReturn([
            'status' => 'ok',
            'database' => 'connected',
            'cache' => 'working',
            'queue' => 'running',
        ]);
        $this->app->instance(SystemHealthService::class, $mock);

        $response = $this->getJson('/api/admin/health');

        $response->assertStatus(200)
                 ->assertJsonFragment(['status' => 'ok'])
                 ->assertJsonStructure(['status','database','cache','queue']);
    }

    /** @test */
    public function test_health_returns_empty_response()
    {
        $this->withoutMiddleware();

        $mock = Mockery::mock(SystemHealthService::class);
        $mock->shouldReceive('get')->once()->andReturn([]);
        $this->app->instance(SystemHealthService::class, $mock);

        $response = $this->getJson('/api/admin/health');

        $response->assertStatus(200)
                 ->assertExactJson([]);
    }

    /** @test */
    public function test_health_handles_error_response()
    {
        $this->withoutMiddleware();

        $mock = Mockery::mock(SystemHealthService::class);
        $mock->shouldReceive('get')->once()->andReturn([
            'status' => 'error',
            'message' => 'Database not reachable',
        ]);
        $this->app->instance(SystemHealthService::class, $mock);

        $response = $this->getJson('/api/admin/health');

        $response->assertStatus(200)
                 ->assertJsonFragment(['status' => 'error'])
                 ->assertJsonFragment(['message' => 'Database not reachable']);
    }
}