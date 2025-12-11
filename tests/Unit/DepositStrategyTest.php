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

  // public function test_deposit_creates_transaction()
  // {
  //   // arrange: account in DB (strategy locks the row)
  //   $account = Account::factory()->create([
  //     'number'  => 'AC123TEST',
  //     'balance' => 200.0000,
  //   ]);

  //   $dto = new ProcessTransactionDTO(
  //     account_id: 'AC123TEST',
  //     amount: 100.00,
  //     type: 'deposit',
  //     account_related_id: null,
  //     description: null,
  //     employee_name: null,
  //     requestedBy: null
  //   );

  //   $strategy = new DepositStrategy();

  //   // act
  //   $txn = $strategy->execute($dto, null);

  //   // assert transaction properties and account balance updated
  //   $this->assertEquals('deposit', $txn->type);
  //   $this->assertEquals(100.00, (float)$txn->amount);

  //   // $this->assertEquals(300.00, (float)$account->fresh()->balance);
  //   $this->assertEquals("300.0000", $account->fresh()->balance);
  // }

  //   public function test_deposit_creates_transaction()
  // {
  //     $account = Account::factory()->create([
  //         'balance' => 200.0000,
  //         'number'  => 'AC_TEST'
  //     ]);

  //     $dto = new ProcessTransactionDTO(
  //         account_id: 'AC_TEST',
  //         amount: 100.00,
  //         type: 'deposit',
  //         account_related_id: null,
  //         description: null,
  //         employee_name: null,
  //         requestedBy: null
  //     );

  //     $strategy = new DepositStrategy;

  //     $txn = $strategy->execute($dto, null);

  //     $this->assertEquals('deposit', $txn->type);
  //     $this->assertEquals(100.00, (float)$txn->amount);
  //     $this->assertEquals("300.0000", $account->fresh()->balance);
  // }


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
