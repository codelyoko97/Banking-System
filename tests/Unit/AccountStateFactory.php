<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AccountStateFactory extends TestCase
{
    use RefreshDatabase;

    public function test_account_state_factory_returns_correct_state()
    {
        $acc = new \App\Models\Account([
            'status_id' => 1
        ]);

        $state = \App\Banking\Transactions\States\AccountStateFactory::make($acc);

        $this->assertNotNull($state);
    }
}
