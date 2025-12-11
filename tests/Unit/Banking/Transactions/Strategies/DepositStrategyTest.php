<?php

namespace Tests\Unit;

use App\Banking\Transactions\Strategies\DepositStrategy;
use App\DTO\ProcessTransactionDTO;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Account;
use Illuminate\Support\Facades\DB;

class DepositStrategyTest extends TestCase
{
  use RefreshDatabase;



  public function test_deposit_creates_transaction()
  {
    $account = Account::factory()->create([
      'balance' => 200.0000,
      'number'  => 'AC_TEST'
    ]);

    // تأكد إن الحساب ما عنده ميزات
    DB::table('account_features')->where('account_id', $account->id)->delete();

    $dto = new ProcessTransactionDTO(
      account_id: 'AC_TEST',
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
}
