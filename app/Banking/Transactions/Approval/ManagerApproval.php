<?php

namespace App\Banking\Transactions\Approval;

use App\DTO\ProcessTransactionDTO;

class ManagerApproval extends AbstractApprovalHandler
{
  protected function canApprove(ProcessTransactionDTO $dto): bool
  {
    return $dto->amount > 10000;
  }
}
