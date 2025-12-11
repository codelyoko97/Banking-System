<?php

namespace Tests\Unit\Banking\Transactions\Approval;

use App\Banking\Transactions\Approval\ManagerApproval;
use App\DTO\ProcessTransactionDTO;
use PHPUnit\Framework\TestCase;

class ManagerApprovalTest extends TestCase
{
  protected function makeDto(float $amount, string $type = 'transfer'): ProcessTransactionDTO
  {
    return new ProcessTransactionDTO(
      account_id: 'ACC789',
      amount: $amount,
      type: $type,
      account_related_id: null,
      description: 'Manager test',
      employee_name: 'Manager',
      requestedBy: null
    );
  }

  public function test_approve_large_amount_executes_strategy()
  {
    $dto = $this->makeDto(20000);
    $manager = $this->getMockBuilder(ManagerApproval::class)
      ->onlyMethods(['strategyFor'])
      ->getMock();

    $transaction = $this->createMock(\App\Models\Transaction::class);
    $strategy = $this->getMockBuilder(\App\Banking\Transactions\Strategies\TransferStrategy::class)
      ->onlyMethods(['execute'])
      ->getMock();
    $strategy->method('execute')->willReturn($transaction);

    $manager->method('strategyFor')->willReturn($strategy);

    $result = $manager->approve($dto);
    $this->assertInstanceOf(\App\Models\Transaction::class, $result);
  }
}
