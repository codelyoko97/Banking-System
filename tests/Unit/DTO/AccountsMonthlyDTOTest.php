<?php

namespace Tests\Unit\DTO;

use App\DTO\Dashboard\AccountsMonthlyDTO;
use PHPUnit\Framework\TestCase;

class AccountsMonthlyDTOTest extends TestCase
{
    public function test_constructor_maps_rows_correctly()
    {
        $rows = [
            ['date' => '2025-01-01', 'count' => '5'],
            ['date' => '2025-01-02', 'count' => '10'],
        ];

        $dto = new AccountsMonthlyDTO($rows);

        $this->assertEquals([
            ['date' => '2025-01-01', 'count' => 5],
            ['date' => '2025-01-02', 'count' => 10],
        ], $dto->data);

        $this->assertIsArray($dto->data);
        $this->assertCount(2, $dto->data);
    }
}