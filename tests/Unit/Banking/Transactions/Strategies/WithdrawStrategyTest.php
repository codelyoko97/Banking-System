<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use App\Repositories\AccountRepositoryInterface;
use App\Services\Accounts\AccountService;

class WithdrawStrategyTest extends TestCase
{
    use RefreshDatabase;

    public function test_withdraw_uses_decorated_balance()
    {
        $repo = Mockery::mock(AccountRepositoryInterface::class);

        $repo->shouldReceive('getAccountById')->andReturn((object)[
            'id' => 1,
            'owner_name' => 'User',
            'balance' => 0.00
        ]);

        $repo->shouldReceive('getFeatures')->andReturn(['overdraft']);

        $service = new AccountService($repo);

        $decorated = $service->getDecoratedAccount(1);

        // adjust expected number according to your OverdraftProtection implementation
        $this->assertEquals(500.00, $decorated->balance);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
