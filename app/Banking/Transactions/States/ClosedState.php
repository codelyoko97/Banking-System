<?php

namespace App\Banking\Transactions\States;

use App\Banking\Transactions\States\AccountStateInterface;
use App\Models\Account;

class ClosedState implements AccountStateInterface
{
  public function deposit(Account $account, float $amount): bool
  {
    throw new \Exception('Cannot deposit to a closed account');
  }

  public function withdraw(Account $account, float $amount): bool
  {
    throw new \Exception('Cannot withdraw from a closed account');
  }

  public function close(Account $account): bool
  {
    throw new \Exception('Account is already closed');
  }

  public function key(): string
  {
    return 'closed';
  }
}
