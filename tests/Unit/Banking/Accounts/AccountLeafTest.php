<?php

namespace Tests\Unit\Banking\Accounts;

use App\Banking\Accounts\AccountLeaf;
use App\Models\Account;
use App\Repositories\AccountRepositoryInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountLeafTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_balance_and_id()
    {
        $account = Account::factory()->create([
            'balance' => 150.00,
        ]);

        $repo = $this->createMock(AccountRepositoryInterface::class);
        $leaf = new AccountLeaf($account, $repo);

        $this->assertEquals($account->id, $leaf->getId());
        $this->assertEquals(150.00, $leaf->getBalance());
        $this->assertEmpty($leaf->getChildren());
    }

    public function test_deposit_adjusts_balance()
    {
        $account = Account::factory()->create([
            'balance' => 100.00,
        ]);

        $repo = $this->createMock(AccountRepositoryInterface::class);
        $repo->expects($this->once())
             ->method('adjustBalance')
             ->with($account, 50.00);

        $leaf = new AccountLeaf($account, $repo);
        $this->assertTrue($leaf->deposit(50.00));
    }

    public function test_withdraw_adjusts_balance()
    {
        $account = Account::factory()->create([
            'balance' => 200.00,
        ]);

        $repo = $this->createMock(AccountRepositoryInterface::class);
        $repo->expects($this->once())
             ->method('adjustBalance')
             ->with($account, -100.00);

        $leaf = new AccountLeaf($account, $repo);
        $this->assertTrue($leaf->withdraw(100.00));
    }

    public function test_withdraw_insufficient_funds_throws_exception()
    {
        $account = Account::factory()->create([
            'balance' => 50.00,
        ]);

        $repo = $this->createMock(AccountRepositoryInterface::class);
        $leaf = new AccountLeaf($account, $repo);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Insufficient funds');

        $leaf->withdraw(100.00);
    }
}