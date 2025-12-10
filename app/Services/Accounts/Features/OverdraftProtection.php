<?php

namespace App\Services\Accounts\Features;

class OverdraftProtection extends AccountDecorator
{
    public function getDescription(): string
    {
        return $this->account->getDescription() . " + Overdraft Protection";
    }

    public function getBalance(): float
    {
        return $this->account->getBalance() + 500;
    }
}
