<?php

namespace Tests\Feature\Repositories;

use App\Repositories\EloquentReportsRepository;
use App\Models\Transaction;
use App\Models\Account;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;
use Tests\TestCase;

class EloquentReportsRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private EloquentReportsRepository $repo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = new EloquentReportsRepository();
    }

    /** @test */
    public function it_gets_transactions_daily()
    {
        // معاملات اليوم
        $todayTx = Transaction::factory()->create([
            'created_at' => Carbon::today()->addHours(2),
        ]);

        // معاملات قديمة
        Transaction::factory()->create([
            'created_at' => Carbon::yesterday(),
        ]);

        $result = $this->repo->getTransactionsDaily();

        $this->assertCount(1, $result);
        $this->assertEquals($todayTx->id, $result[0]['id']);
    }

    /** @test */
    public function it_gets_transactions_weekly()
    {
        $recentTx = Transaction::factory()->create([
            'created_at' => Carbon::now()->subDays(3),
        ]);

        $oldTx = Transaction::factory()->create([
            'created_at' => Carbon::now()->subDays(10),
        ]);

        $result = $this->repo->getTransactionsWeekly();

        $ids = array_column($result, 'id');
        $this->assertContains($recentTx->id, $ids);
        $this->assertNotContains($oldTx->id, $ids);
    }

    /** @test */
    public function it_gets_transactions_monthly()
    {
        $recentTx = Transaction::factory()->create([
            'created_at' => Carbon::now()->subDays(15),
        ]);

        $oldTx = Transaction::factory()->create([
            'created_at' => Carbon::now()->subDays(40),
        ]);

        $result = $this->repo->getTransactionsMonthly();

        $ids = array_column($result, 'id');
        $this->assertContains($recentTx->id, $ids);
        $this->assertNotContains($oldTx->id, $ids);
    }

    /** @test */
    public function it_gets_account_summaries()
    {
        $customer = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '123456789',
        ]);

        $account = Account::factory()->create([
            'customer_id' => $customer->id,
            'balance' => 500.75,
        ]);

        // معاملات مرتبطة بالحساب
        Transaction::factory()->count(7)->create([
            'account_id' => $account->id,
        ]);

        $result = $this->repo->getAccountSummaries();

        $this->assertCount(1, $result);

        $summary = $result[0];
        $this->assertEquals($account->id, $summary['account_id']);
        $this->assertEquals($account->number, $summary['account_number']);
        $this->assertEquals(500.75, $summary['balance']);
        $this->assertEquals($customer->id, $summary['customer']['id']);
        $this->assertEquals('John Doe', $summary['customer']['name']);
        $this->assertEquals('john@example.com', $summary['customer']['email']);
        $this->assertEquals('123456789', $summary['customer']['phone']);

        $this->assertEquals(7, $summary['transactions_count']);

        $this->assertCount(5, $summary['latest_transactions']);
    }
}