<?php

namespace App\Banking\Transactions\States;

use App\Banking\Transactions\States\AccountStateInterface;
use App\Models\Account;

class FrozenState implements AccountStateInterface
{
  public function deposit(Account $account, float $amount): bool
  {
    $account->balance = bcadd($account->balance, (string)$amount, 4);
    $account->save();
    return true;
  }

  public function withdraw(Account $account, float $amount): bool
  {
    throw new \Exception('Withdrawals are not allowed');
  }

  public function close(Account $account): bool
  {
    throw new \Exception('Cannot close this account');
  }

  public function key(): string
  {
    return 'frozen';
  }
}
