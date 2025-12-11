<?php

namespace Tests\Unit\Banking\Transactions\Approval;

use App\Banking\Transactions\Approval\TellerApproval;
use App\DTO\ProcessTransactionDTO;
use PHPUnit\Framework\TestCase;

class TellerApprovalTest extends TestCase
{
  protected function makeDto(float $amount, string $type = 'withdraw'): ProcessTransactionDTO
  {
    return new ProcessTransactionDTO(
      account_id: 'ACC456',
      amount: $amount,
      type: $type,
      account_related_id: null,
      description: 'Teller test',
      employee_name: 'Teller',
      requestedBy: null
    );
  }

  public function test_approve_medium_amount_executes_strategy()
  {
    $dto = $this->makeDto(5000);
    $teller = $this->getMockBuilder(TellerApproval::class)
      ->onlyMethods(['strategyFor'])
      ->getMock();

    $transaction = $this->createMock(\App\Models\Transaction::class);
    $strategy = $this->getMockBuilder(\App\Banking\Transactions\Strategies\WithdrawStrategy::class)
      ->onlyMethods(['execute'])
      ->getMock();
    $strategy->method('execute')->willReturn($transaction);

    $teller->method('strategyFor')->willReturn($strategy);

    $result = $teller->approve($dto);
    $this->assertInstanceOf(\App\Models\Transaction::class, $result);
  }
}
