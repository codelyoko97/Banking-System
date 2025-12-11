<?php

namespace Tests\Unit\Banking\Transactions\Strategies;

use App\Banking\Transactions\Strategies\TransactionStrategy;
use App\Banking\Transactions\Strategies\TransferStrategy;
use App\DTO\ProcessTransactionDTO;
use App\Models\Account;
use App\Models\Transaction;
use DomainException;
use PHPUnit\Framework\TestCase;

class TransactionStrategyTest extends TestCase
{
    public function test_transfer_strategy_implements_interface()
    {
        $strategy = new TransferStrategy();
        $this->assertInstanceOf(TransactionStrategy::class, $strategy);
    }
}