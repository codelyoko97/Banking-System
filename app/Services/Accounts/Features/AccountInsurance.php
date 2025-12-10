<?php

namespace App\Services\Accounts\Features;

class AccountInsurance extends AccountDecorator
{
    public function getDescription(): string
    {
        return $this->account->getDescription() . " + Insurance";
    }
}
