<?php

namespace Tests\Unit\DTO;

use App\DTO\Dashboard\TopCustomersDTO;
use PHPUnit\Framework\TestCase;

class TopCustomersDTOTest extends TestCase
{
    public function test_constructor_maps_rows_correctly()
    {
        $rows = [
            [
                'user_id' => 1,
                'name' => 'Alice',
                'email' => 'alice@example.com',
                'total_amount' => '1500.75',
                'tx_count' => '12',
            ],
            [
                'user_id' => 2,
                'name' => 'Bob',
                'email' => 'bob@example.com',
                'total_amount' => '250.50',
                'tx_count' => '3',
            ],
        ];

        $dto = new TopCustomersDTO($rows);

        $this->assertEquals([
            [
                'user_id' => 1,
                'name' => 'Alice',
                'email' => 'alice@example.com',
                'total_amount' => 1500.75,
                'tx_count' => 12,
            ],
            [
                'user_id' => 2,
                'name' => 'Bob',
                'email' => 'bob@example.com',
                'total_amount' => 250.50,
                'tx_count' => 3,
            ],
        ], $dto->data);

        $this->assertCount(2, $dto->data);
    }
}