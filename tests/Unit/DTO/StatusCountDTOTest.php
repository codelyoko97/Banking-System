<?php

namespace Tests\Unit\DTO;

use App\DTO\Dashboard\StatusCountDTO;
use PHPUnit\Framework\TestCase;

class StatusCountDTOTest extends TestCase
{
    public function test_constructor_maps_rows_correctly()
    {
        $rows = [
            ['status' => 'active', 'count' => '5'],
            ['status' => 'closed', 'count' => '10'],
        ];

        $dto = new StatusCountDTO($rows);

        $this->assertEquals([
            ['status' => 'active', 'count' => 5],
            ['status' => 'closed', 'count' => 10],
        ], $dto->data);

        $this->assertCount(2, $dto->data);
    }
}