<?php

namespace Tests\Unit\DTO;

use App\DTO\AccountSummaryDTO;
use PHPUnit\Framework\TestCase;

class AccountSummaryDTOTest extends TestCase
{
    public function test_constructor_sets_data()
    {
        $rows = [
            ['id' => 1, 'balance' => 100.00],
            ['id' => 2, 'balance' => 200.00],
        ];

        $dto = new AccountSummaryDTO($rows);

        $this->assertEquals($rows, $dto->data);
        $this->assertCount(2, $dto->data);
    }
}