<?php

namespace App\Services\Accounts\Features;

class PremiumService extends AccountDecorator
{
    public function getDescription(): string
    {
        return $this->account->getDescription() . " + Premium Service";
    }
}
