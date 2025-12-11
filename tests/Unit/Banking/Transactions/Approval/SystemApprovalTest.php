<?php

namespace Tests\Unit\Banking\Transactions\Approval;

use App\Banking\Transactions\Approval\SystemApproval;
use App\DTO\ProcessTransactionDTO;
use PHPUnit\Framework\TestCase;

class SystemApprovalTest extends TestCase
{
    protected function makeDto(float $amount, string $type = 'deposit'): ProcessTransactionDTO
    {
        return new ProcessTransactionDTO(
            account_id: 'ACC123',
            amount: $amount,
            type: $type,
            account_related_id: null,
            description: 'System test',
            employee_name: 'System',
            requestedBy: null
        );
    }

    public function test_approve_small_amount_executes_strategy()
    {
        $dto = $this->makeDto(500);
        $system = $this->getMockBuilder(SystemApproval::class)
                       ->onlyMethods(['strategyFor'])
                       ->getMock();

        $transaction = $this->createMock(\App\Models\Transaction::class);
        $strategy = $this->getMockBuilder(\App\Banking\Transactions\Strategies\DepositStrategy::class)
                         ->onlyMethods(['execute'])
                         ->getMock();
        $strategy->method('execute')->willReturn($transaction);

        $system->method('strategyFor')->willReturn($strategy);

        $result = $system->approve($dto);
        $this->assertInstanceOf(\App\Models\Transaction::class, $result);
    }
}