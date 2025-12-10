<?php

namespace App\Services\Accounts\Features;

abstract class AccountDecorator implements AccountInterface
{
    protected AccountInterface $account;

    public function __construct(AccountInterface $account)
    {
        $this->account = $account;
    }

    public function getDescription(): string
    {
        return $this->account->getDescription();
    }

    public function getBalance(): float
    {
        return $this->account->getBalance();
    }
}
