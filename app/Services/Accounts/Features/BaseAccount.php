<?php

namespace App\Services\Accounts\Features;

class BaseAccount implements AccountInterface
{
    protected float $balance;
    protected string $owner;

    public function __construct(?string $owner, float $balance)
    {
        $this->owner = $owner ?? 'Unknown';
        $this->balance = $balance;
    }

    public function getDescription(): string
    {
        return "Basic Account for {$this->owner}";
    }

    public function getBalance(): float
    {
        return $this->balance;
    }
}
