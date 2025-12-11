<?php

namespace Tests\Unit\DTO;

use App\DTO\Dashboard\WeeklyTransactionsDTO;
use PHPUnit\Framework\TestCase;

class WeeklyTransactionsDTOTest extends TestCase
{
    public function test_constructor_maps_rows_correctly()
    {
        $rows = [
            ['day' => 'Monday', 'total' => '25'],
            ['day' => 'Tuesday', 'total' => '40'],
        ];

        $dto = new WeeklyTransactionsDTO($rows);

        $this->assertEquals([
            ['day' => 'Monday', 'total' => 25],
            ['day' => 'Tuesday', 'total' => 40],
        ], $dto->data);

        $this->assertCount(2, $dto->data);
    }
}