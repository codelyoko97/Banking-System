<?php

namespace Tests\Unit\DTO;

use App\DTO\Dashboard\Transaction24hDTO;
use PHPUnit\Framework\TestCase;

class Transaction24hDTOTest extends TestCase
{
    public function test_build_returns_correct_array()
    {
        $row = [
            'total' => '100',
            'success' => '80',
            'failed' => '15',
            'pending' => '5',
        ];

        $result = Transaction24hDTO::build($row);

        $this->assertEquals([
            'total' => 100,
            'success' => 80,
            'failed' => 15,
            'pending' => 5,
        ], $result);
    }
}