<?php

namespace App\Banking\Transactions\States;

use App\Banking\Transactions\States\AccountStateInterface;
use App\Models\Account;
use App\Models\Log;
use App\Models\Status;

class ActiveState implements AccountStateInterface
{
  public function deposit(Account $account, float $amount): bool
  {
    $account->balance = bcadd($account->balance, (string)$amount, 4);
    $account->save();
    Log::create([
      'user_id' => $account->customer_id,
      'action' => 'deposit',
      'description' => "Deposit {$amount} to account {$account->number} (active)"
    ]);
    return true;
  }

  public function withdraw(Account $account, float $amount): bool
  {
    if (bccomp((string)$account->balance, (string)$amount, 4) < 0) {
      throw new \Exception('Insufficient funds');
    }
    $account->balance = bcsub($account->balance, (string)$amount, 4);
    $account->save();
    Log::create([
      'user_id' => $account->customer_id,
      'action' => 'withdraw',
      'description' => "Withdraw {$amount} from account {$account->number} (active)"
    ]);
    return true;
  }

  public function close(Account $account): bool
  {
    if (bccomp((string)$account->balance, '0.0000', 4) !== 0) {
      throw new \Exception('Account balance must be zero to close.');
    }
    $closedStatus = Status::where('name', 'closed')->first();
    if (!$closedStatus) throw new \Exception('Closed status not defined');
    $account->status_id = $closedStatus->id;
    $account->save();
    Log::create([
      'user_id' => $account->customer_id,
      'action' => 'close',
      'description' => "Account {$account->number} closed."
    ]);
    return true;
  }

  public function key(): string
  {
    return 'active';
  }
}
