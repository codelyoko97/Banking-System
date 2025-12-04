<?php
namespace App\Banking\Transactions\States;

use App\Models\Account;

interface AccountStateInterface {
    public function deposit(Account $account, float $amount): bool;
    public function withdraw(Account $account, float $amount): bool;
    public function close(Account $account): bool;
    public function key(): string;
}
