<?php

namespace App\Banking\Transactions\Approval;

use App\Banking\Transactions\Strategies\DepositStrategy;
use App\Banking\Transactions\Strategies\TransactionStrategy;
use App\Banking\Transactions\Strategies\TransferStrategy;
use App\Banking\Transactions\Strategies\WithdrawStrategy;
use App\DTO\ProcessTransactionDTO;
use App\Models\Transaction;
use DomainException;

abstract class AbstractApprovalHandler implements ApprovalHandler
{
  protected ?ApprovalHandler $next = null;
  protected ?int $id = null;

  public function setNext(?ApprovalHandler $next, ?int $id): ApprovalHandler
  {
    $this->next = $next;
    if ($next !== null) {
      $next->setId($id);
    }
    return $next ?? $this;
  }

  public function setId(?int $id): void
  {
    $this->id = $id;
  }

  public function approve(ProcessTransactionDTO $dto): Transaction
  {
    if ($this->canApprove($dto)) {
      $strategy = $this->strategyFor($dto->type);
      return $strategy->execute($dto, $this->id);
    }

    if ($this->next) {
      return $this->next->approve($dto);
    }

    $strategy = $this->strategyFor($dto->type);
    return $strategy->execute($dto, $this->id);
  }


  abstract protected function canApprove(ProcessTransactionDTO $dto): bool;

  public function strategyFor(string $type): TransactionStrategy
  {
    return match ($type) {
      'deposit' => new DepositStrategy(),
      'withdraw' => new WithdrawStrategy(),
      'transfer' => new TransferStrategy(),
      'invoice' => new TransferStrategy(),
      default => throw new DomainException('Unsupported transaction type')
    };
  }
}
