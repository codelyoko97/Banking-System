<?php

namespace Tests\Unit\Banking\Transactions\Approval;

use App\Banking\Transactions\Approval\SystemApproval;
use DomainException;
use PHPUnit\Framework\TestCase;

class AbstractApprovalHandlerTest extends TestCase
{
    public function test_set_next_and_set_id()
    {
        $system = new SystemApproval();
        $next = new SystemApproval();

        $system->setNext($next, 99);

        $reflection = new \ReflectionClass($next);
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);

        $this->assertEquals(99, $property->getValue($next));
    }

    public function test_strategy_for_supported_types()
    {
        $system = new SystemApproval();

        $this->assertInstanceOf(\App\Banking\Transactions\Strategies\DepositStrategy::class, $system->strategyFor('deposit'));
        $this->assertInstanceOf(\App\Banking\Transactions\Strategies\WithdrawStrategy::class, $system->strategyFor('withdraw'));
        $this->assertInstanceOf(\App\Banking\Transactions\Strategies\TransferStrategy::class, $system->strategyFor('transfer'));
        $this->assertInstanceOf(\App\Banking\Transactions\Strategies\TransferStrategy::class, $system->strategyFor('invoice'));
    }

    public function test_strategy_for_unsupported_type_throws_exception()
    {
        $system = new SystemApproval();
        $this->expectException(DomainException::class);
        $system->strategyFor('unsupported');
    }
}