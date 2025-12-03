<?php
// app/Banking/Accounts/States/FrozenState.php
namespace App\Banking\Accounts\States;

use App\Models\Account;

class FrozenState implements AccountStateInterface {
    public function deposit(Account $account, float $amount): bool {
        $account->balance = bcadd($account->balance, (string)$amount, 4);
        $account->save();
        \App\Models\Log::create([
            'user_id'=>$account->customer_id,
            'action'=>'deposit',
            'description'=>"Deposit {$amount} to frozen account {$account->number}"
        ]);
        return true;
    }

    public function withdraw(Account $account, float $amount): bool {
        throw new \Exception('Account is frozen; withdrawals are not allowed');
    }

    public function close(Account $account): bool {
        throw new \Exception('Cannot close a frozen account; unfreeze first');
    }

    public function key(): string { return 'frozen'; }
}
