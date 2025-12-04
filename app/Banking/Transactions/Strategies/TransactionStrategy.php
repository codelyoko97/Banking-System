<?php

namespace App\Banking\Transactions\Strategies;

use App\DTO\ProcessTransactionDTO;
use App\Models\Account;
use App\Models\Transaction;

interface TransactionStrategy
{
  public function execute(ProcessTransactionDTO $dto, ?int $id): Transaction;
  public function executeFromTransaction(Transaction $transaction, Account $account);
}
