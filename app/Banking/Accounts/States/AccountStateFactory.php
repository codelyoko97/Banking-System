<?php

namespace App\Banking\Accounts\States;

use App\Models\Account;

class AccountStateFactory {
    public static function make(Account $account): AccountStateInterface {
        $name = optional($account->status)->name;
        return match($name) {
            'active' => new ActiveState(),
            'frozen' => new FrozenState(),
            // 'suspended' => new SuspendedState(), 
            // 'closed' => new ClosedState(),
            default => new ActiveState(),
        };
    }
}
