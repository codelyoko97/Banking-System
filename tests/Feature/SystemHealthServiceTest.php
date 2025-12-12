<?php

namespace Tests\Feature;

use App\Services\SystemHealthService;
use App\Repositories\SystemHealthRepository;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;
use Mockery;

class SystemHealthServiceTest extends TestCase
{
    protected $repo;
    protected $service;

    public function setUp(): void
    {
        parent::setUp();
        Cache::flush(); // تنظيف الكاش قبل كل اختبار
        $this->repo = Mockery::mock(SystemHealthRepository::class);
        $this->service = new SystemHealthService($this->repo);
    }

    /** @test */
    public function it_returns_health_status_array_with_all_ok()
    {
        $this->repo->shouldReceive('dbStatus')->once()->andReturn(['status' => 'ok']);
        $this->repo->shouldReceive('cacheStatus')->once()->andReturn(['status' => 'ok']);
        $this->repo->shouldReceive('queueStatus')->once()->andReturn(['status' => 'ok']);
        $this->repo->shouldReceive('requestStats')->once()->andReturn(['requests' => 100]);
        $this->repo->shouldReceive('systemUsage')->once()->andReturn(['cpu' => 20, 'memory' => 50]);

        $result = $this->service->get();

        $this->assertEquals(['status' => 'ok'], $result['db']);
        $this->assertEquals(['status' => 'ok'], $result['cache']);
        $this->assertEquals(['status' => 'ok'], $result['queue']);
        $this->assertEquals(['requests' => 100], $result['requests']);
        $this->assertEquals(['cpu' => 20, 'memory' => 50], $result['system']);
    }

    /** @test */
    public function it_returns_health_status_with_varied_values()
    {
        $this->repo->shouldReceive('dbStatus')->once()->andReturn(['status' => 'down']);
        $this->repo->shouldReceive('cacheStatus')->once()->andReturn(['status' => 'miss']);
        $this->repo->shouldReceive('queueStatus')->once()->andReturn(['status' => 'stalled']);
        $this->repo->shouldReceive('requestStats')->once()->andReturn(['requests' => 0]);
        $this->repo->shouldReceive('systemUsage')->once()->andReturn(['cpu' => 90, 'memory' => 95]);

        $result = $this->service->get();

        $this->assertEquals(['status' => 'down'], $result['db']);
        $this->assertEquals(['status' => 'miss'], $result['cache']);
        $this->assertEquals(['status' => 'stalled'], $result['queue']);
        $this->assertEquals(['requests' => 0], $result['requests']);
        $this->assertEquals(['cpu' => 90, 'memory' => 95], $result['system']);
    }

    /** @test */
    public function it_handles_null_values()
    {
        $this->repo->shouldReceive('dbStatus')->once()->andReturn(['status' => null]);
        $this->repo->shouldReceive('cacheStatus')->once()->andReturn(['status' => null]);
        $this->repo->shouldReceive('queueStatus')->once()->andReturn(['status' => null]);
        $this->repo->shouldReceive('requestStats')->once()->andReturn(['requests' => null]);
        $this->repo->shouldReceive('systemUsage')->once()->andReturn(['cpu' => null, 'memory' => null]);

        $result = $this->service->get();

        $this->assertEquals(['status' => null], $result['db']);
        $this->assertEquals(['status' => null], $result['cache']);
        $this->assertEquals(['status' => null], $result['queue']);
        $this->assertEquals(['requests' => null], $result['requests']);
        $this->assertEquals(['cpu' => null, 'memory' => null], $result['system']);
    }

    /** @test */
    public function it_uses_cache_on_second_call()
    {
        $this->repo->shouldReceive('dbStatus')->once()->andReturn(['status' => 'ok']);
        $this->repo->shouldReceive('cacheStatus')->once()->andReturn(['status' => 'ok']);
        $this->repo->shouldReceive('queueStatus')->once()->andReturn(['status' => 'ok']);
        $this->repo->shouldReceive('requestStats')->once()->andReturn(['requests' => 50]);
        $this->repo->shouldReceive('systemUsage')->once()->andReturn(['cpu' => 10, 'memory' => 20]);

        $first = $this->service->get();
        $second = $this->service->get();

        $this->assertEquals($first, $second);
    }

    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}