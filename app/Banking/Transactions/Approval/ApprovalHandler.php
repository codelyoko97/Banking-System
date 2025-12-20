<?php

namespace App\Banking\Transactions\Approval;

use App\DTO\ProcessTransactionDTO;
use App\Models\Transaction;

interface ApprovalHandler
{
  public function setNext(?ApprovalHandler $next, ?int $id): ApprovalHandler;
  public function SetId(?int $id): void;
  public function approve(ProcessTransactionDTO $dto): Transaction;
}