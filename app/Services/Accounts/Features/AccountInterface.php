<?php

namespace App\Services\Accounts\Features;

interface AccountInterface
{
    public function getDescription(): string;
    public function getBalance(): float;
}
