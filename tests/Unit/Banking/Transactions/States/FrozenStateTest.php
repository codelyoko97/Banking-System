<?php

namespace Tests\Unit\Banking\Transactions\States;

use App\Banking\Transactions\States\FrozenState;
use App\Models\Account;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FrozenStateTest extends TestCase
{
    use RefreshDatabase;

    public function test_deposit_in_frozen_state_increases_balance()
    {
        $account = Account::factory()->create(['balance' => 100.00]);

        $state = new FrozenState();
        $result = $state->deposit($account, 50.00);

        $this->assertTrue($result);
        $this->assertEquals(150.00, (float)$account->fresh()->balance);
    }

    public function test_withdraw_in_frozen_state_throws_exception()
    {
        $account = Account::factory()->create(['balance' => 100.00]);

        $state = new FrozenState();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Withdrawals are not allowed');

        $state->withdraw($account, 50.00);
    }

    public function test_close_in_frozen_state_throws_exception()
    {
        $account = Account::factory()->create(['balance' => 0.00]);

        $state = new FrozenState();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot close this account');

        $state->close($account);
    }

    public function test_key_returns_frozen()
    {
        $state = new FrozenState();
        $this->assertEquals('frozen', $state->key());
    }
}