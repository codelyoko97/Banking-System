<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Banking\Transactions\Strategies\WithdrawStrategy;
use App\DTO\ProcessTransactionDTO;
use App\Models\Account;
use DomainException;

class WithdrawStrategyErrorsTest extends TestCase
{
    use RefreshDatabase;

    public function test_withdraw_fails_if_insufficient()
    {
        $this->expectException(DomainException::class);

        // arrange: account with zero balance and no features
        $account = Account::factory()->create([
            'number' => 'AC_ZERO',
            'balance' => 0.00,
        ]);

        $dto = new ProcessTransactionDTO(
            account_id: 'AC_ZERO',
            amount: 500.00,
            type: 'withdraw',
            account_related_id: null,
            description: null,
            employee_name: null,
            requestedBy: null
        );

        $strategy = new WithdrawStrategy();

        // act: should throw DomainException
        $strategy->execute($dto, null);
    }
}
