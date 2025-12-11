<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Banking\Transactions\Strategies\WithdrawStrategy;
use App\DTO\ProcessTransactionDTO;
use App\Models\Account;
use Illuminate\Support\Facades\DB;

class WithdrawStrategyErrorsTest extends TestCase
{
  use RefreshDatabase;

  protected function setUp(): void
  {
    parent::setUp();
    DB::table('account_features')->truncate();
  }
  public function test_withdraw_fails_if_insufficient_balance()
  {
    $account = Account::factory()->create([
      'balance' => 0.0000,
      'number'  => 'AC_ZERO'
    ]);

    $dto = new ProcessTransactionDTO(
      account_id: 'AC_ZERO',
      amount: 100.00,
      type: 'withdraw',
      account_related_id: null,
      description: null,
      employee_name: null,
      requestedBy: null
    );

    $this->expectException(\DomainException::class);

    $strategy = new WithdrawStrategy;
    $strategy->execute($dto, null);
  }

  public function test_withdraw_allows_with_overdraft_feature()
  {
    $account = Account::factory()->create([
      'balance' => 100.00,
      'number'  => 'AC_OD'
    ]);

    // أضف overdraft
    DB::table('account_features')->insert([
      'account_id' => $account->id,
      'feature'    => 'overdraft'
    ]);

    $dto = new ProcessTransactionDTO(
      account_id: 'AC_OD',
      amount: 500.00, // أكبر من الرصيد لكن ضمن overdraft
      type: 'withdraw',
      account_related_id: null,
      description: null,
      employee_name: null,
      requestedBy: null
    );

    $strategy = new WithdrawStrategy;
    $txn = $strategy->execute($dto, null);

    $this->assertEquals('withdraw', $txn->type);
    $this->assertEquals(500.00, (float)$txn->amount);
  }
}
