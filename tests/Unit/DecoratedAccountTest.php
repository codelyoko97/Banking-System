<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use App\Repositories\AccountRepositoryInterface;
use App\Services\Accounts\AccountService;

class DecoratedAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_decorated_account_with_overdraft()
    {
        $repo = Mockery::mock(AccountRepositoryInterface::class);

        $repo->shouldReceive('getAccountById')->once()->andReturn((object)[
            'id' => 1,
            'owner_name' => "Test User",
            'balance' => 100.00
        ]);

        $repo->shouldReceive('getFeatures')->once()->andReturn(['overdraft']);

        $service = new AccountService($repo);

        $dto = $service->getDecoratedAccount(1);

        // Adjust expected value to match your OverdraftProtection implementation
        $this->assertEquals(600.00, $dto->balance); // 100 + 500 overdraft (example)
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
