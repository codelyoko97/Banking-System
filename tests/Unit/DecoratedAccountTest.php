<?php

namespace Tests\Unit;

use App\Banking\Transactions\Strategies\DepositStrategy;
use App\DTO\ProcessTransactionDTO;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Account;
use Illuminate\Support\Facades\DB;

class DecoratedAccountTest extends TestCase
{
  use RefreshDatabase;

  protected function setUp(): void
  {
    parent::setUp();
    DB::table('account_features')->truncate();
  }

  public function test_deposit_without_features()
  {
    $account = Account::factory()->create([
      'balance' => 200.0000,
      'number'  => 'AC_NOFEATURE'
    ]);

    $dto = new ProcessTransactionDTO(
      account_id: 'AC_NOFEATURE',
      amount: 100.00,
      type: 'deposit',
      account_related_id: null,
      description: null,
      employee_name: null,
      requestedBy: null
    );

    $strategy = new DepositStrategy;
    $txn = $strategy->execute($dto, null);

    $this->assertEquals('deposit', $txn->type);
    $this->assertEquals(100.00, (float)$txn->amount);
    $this->assertEquals("300.0000", $account->fresh()->balance);
  }

  public function test_deposit_with_premium_feature()
  {
    $account = Account::factory()->create([
      'balance' => 200.0000,
      'number'  => 'AC_PREMIUM'
    ]);

    // أضف ميزة premium
    DB::table('account_features')->insert([
      'account_id' => $account->id,
      'feature'    => 'premium'
    ]);

    $dto = new ProcessTransactionDTO(
      account_id: 'AC_PREMIUM',
      amount: 100.00,
      type: 'deposit',
      account_related_id: null,
      description: null,
      employee_name: null,
      requestedBy: null
    );

    $strategy = new DepositStrategy;
    $txn = $strategy->execute($dto, null);

    // المفروض يضيف 1% bonus → 101
    $this->assertEquals("301.0000", $account->fresh()->balance);
  }

  public function test_deposit_with_insurance_feature()
  {
    $account = Account::factory()->create([
      'balance' => 200.0000,
      'number'  => 'AC_INSURANCE'
    ]);

    DB::table('account_features')->insert([
      'account_id' => $account->id,
      'feature'    => 'insurance'
    ]);

    $dto = new ProcessTransactionDTO(
      account_id: 'AC_INSURANCE',
      amount: 100.00,
      type: 'deposit',
      account_related_id: null,
      description: null,
      employee_name: null,
      requestedBy: null
    );

    $strategy = new DepositStrategy;
    $txn = $strategy->execute($dto, null);

    // خصم 0.5% → 99.5
    $this->assertEquals("299.5000", $account->fresh()->balance);
  }
}
