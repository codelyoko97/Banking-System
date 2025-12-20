<?php

namespace App\Banking\Transactions\States;

use App\Banking\Transactions\States\AccountStateInterface;
use App\Models\Account;

class SuspendedState implements AccountStateInterface
{
  public function deposit(Account $account, float $amount): bool
  {
    throw new \Exception('Deposits are not allowed to a suspended account');
  }

  public function withdraw(Account $account, float $amount): bool
  {
    throw new \Exception('Withdrawals are not allowed from a suspended account');
  }

  public function close(Account $account): bool
  {
    throw new \Exception('Cannot close this account');
  }

  public function key(): string
  {
    return 'suspended';
  }
}
