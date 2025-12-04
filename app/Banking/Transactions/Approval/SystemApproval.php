<?php

namespace App\Banking\Transactions\Approval;

use App\DTO\ProcessTransactionDTO;

class SystemApproval extends AbstractApprovalHandler
{
  protected function canApprove(ProcessTransactionDTO $dto): bool
  {
    return $dto->amount <= 1000;
  }
}
