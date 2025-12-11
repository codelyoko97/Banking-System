<?php

namespace Tests\Unit\DTO;

use App\DTO\TransactionReportDTO;
use PHPUnit\Framework\TestCase;

class TransactionReportDTOTest extends TestCase
{
    public function test_constructor_maps_rows_correctly()
    {
        $rows = [
            [
                'id' => 1,
                'account_id' => 101,
                'status' => 'success',
                'amount' => '250.75',
                'type' => 'deposit',
                'employee_name' => 'Alice',
                'created_at' => '2025-01-01 12:00:00',
                'description' => 'Deposit successful',
            ],
            [
                'id' => 2,
                'account_id' => 102,
                'status' => 'failed',
                'amount' => '100.00',
                'type' => 'withdraw',
                'employee_name' => null,
                'created_at' => '2025-01-02 15:30:00',
                'description' => null,
            ],
        ];

        $dto = new TransactionReportDTO($rows);

        $this->assertEquals([
            [
                'id' => 1,
                'account_id' => 101,
                'status' => 'success',
                'amount' => 250.75,
                'type' => 'deposit',
                'employee' => 'Alice',
                'date' => '2025-01-01 12:00:00',
                'description' => 'Deposit successful',
            ],
            [
                'id' => 2,
                'account_id' => 102,
                'status' => 'failed',
                'amount' => 100.00,
                'type' => 'withdraw',
                'employee' => null,
                'date' => '2025-01-02 15:30:00',
                'description' => null,
            ],
        ], $dto->data);

        $this->assertCount(2, $dto->data);
    }
}