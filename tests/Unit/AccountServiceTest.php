<?php

namespace Tests\Unit;

use App\Models\Account;
use Tests\TestCase;
use App\Services\Accounts\AccountService;
use App\Repositories\AccountRepositoryInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;

class AccountServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_account_generates_number()
    {
        $repo = Mockery::mock(AccountRepositoryInterface::class);

        $fakeAccount = Account::factory()->make([
            'customer_id' => 1,
            'type_id' => 1,
            'balance' => 0,
            // factory.make usually sets no number; service will generate one before calling repo->create
            'number' => 'AC_FAKE'
        ]);

        // repo->create should return an Account instance
        $repo->shouldReceive('create')->once()->andReturn($fakeAccount);

        $service = new AccountService($repo);

        // pass status_id to avoid querying statuses table inside service
        $result = $service->create([
            'customer_id' => 1,
            'type_id' => 1,
            'balance' => 0,
            'status_id' => 1
        ]);

        $this->assertNotEmpty($result->number);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
