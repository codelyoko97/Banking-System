<?php

namespace Tests\Unit\DTO;

use App\DTO\Health\SystemHealthDTO;
use PHPUnit\Framework\TestCase;

class SystemHealthDTOTest extends TestCase
{
    public function test_constructor_sets_properties()
    {
        $db = ['status' => 'ok', 'connections' => 10];
        $cache = ['status' => 'ok', 'hits' => 200];
        $queue = ['status' => 'ok', 'jobs' => 5];
        $requests = ['status' => 'ok', 'count' => 100];
        $system = ['cpu' => '20%', 'memory' => '512MB'];

        $dto = new SystemHealthDTO($db, $cache, $queue, $requests, $system);

        $this->assertEquals($db, $dto->db);
        $this->assertEquals($cache, $dto->cache);
        $this->assertEquals($queue, $dto->queue);
        $this->assertEquals($requests, $dto->requests);
        $this->assertEquals($system, $dto->system);
    }

    public function test_to_array_returns_correct_structure()
    {
        $dto = new SystemHealthDTO(
            ['status' => 'ok'],
            ['status' => 'ok'],
            ['status' => 'ok'],
            ['status' => 'ok'],
            ['cpu' => '10%', 'memory' => '256MB']
        );

        $array = $dto->toArray();

        $this->assertArrayHasKey('db', $array);
        $this->assertArrayHasKey('cache', $array);
        $this->assertArrayHasKey('queue', $array);
        $this->assertArrayHasKey('requests', $array);
        $this->assertArrayHasKey('system', $array);

        $this->assertEquals('ok', $array['db']['status']);
        $this->assertEquals('10%', $array['system']['cpu']);
    }

    public function test_edge_case_empty_arrays()
    {
        $dto = new SystemHealthDTO([], [], [], [], []);

        $array = $dto->toArray();

        $this->assertEquals([], $array['db']);
        $this->assertEquals([], $array['cache']);
        $this->assertEquals([], $array['queue']);
        $this->assertEquals([], $array['requests']);
        $this->assertEquals([], $array['system']);
    }

    public function test_edge_case_unexpected_keys()
    {
        $dto = new SystemHealthDTO(
            ['unexpected' => 'value'],
            ['cache_status' => 'down'],
            ['jobs_pending' => 99],
            ['requests_per_second' => 500],
            ['disk' => '90%']
        );

        $array = $dto->toArray();

        $this->assertEquals('value', $array['db']['unexpected']);
        $this->assertEquals('down', $array['cache']['cache_status']);
        $this->assertEquals(99, $array['queue']['jobs_pending']);
        $this->assertEquals(500, $array['requests']['requests_per_second']);
        $this->assertEquals('90%', $array['system']['disk']);
    }
}