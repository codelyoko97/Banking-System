<?php

namespace Tests\Feature\Repositories;

use App\Repositories\EloquentTransactionRepository;
use App\Models\Transaction;
use App\Models\Account;
use App\Models\User;
use App\Models\SchedualeTransaction;
use App\Models\Notification;
use App\Events\TransactionRejected;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;
use Carbon\Carbon;

class EloquentTransactionRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private EloquentTransactionRepository $repo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = new EloquentTransactionRepository();
        Event::fake();
    }

    /** @test */
    public function it_finds_transaction_by_id()
    {
        $txn = Transaction::factory()->create();
        $result = $this->repo->find($txn->id);
        $this->assertEquals($txn->id, $result->id);
    }

    /** @test */
    public function it_rejects_transaction_successfully()
    {
        $user = User::factory()->create(['role_id' => 2]);
        $this->actingAs($user);

        $account = Account::factory()->create(['customer_id' => $user->id]);
        $txn = Transaction::factory()->create([
            'account_id' => $account->id,
            'role_id' => 2,
            'status' => 'pending',
        ]);

        $result = $this->repo->reject($txn->id);

        $this->assertEquals('Transaction rejected successfully', $result['message']);
        $this->assertEquals('rejected', $txn->fresh()->status);
        Event::assertDispatched(TransactionRejected::class);
    }

    /** @test */
    // public function it_creates_schedule_transaction()
    // {
    //     $account = Account::factory()->create(['number' => 'ACC123']);
    //     $related = Account::factory()->create(['number' => 'ACC456']);

    //     $data = [
    //         'account_id' => 'ACC123',
    //         'account_related_id' => 'ACC456',
    //         'amount' => 100,
    //         'type' => 'debit',
    //         'status' => 'pending',
    //     ];

    //     $schedule = $this->repo->createSchedule($data);

    //     $this->assertInstanceOf(SchedualeTransaction::class, $schedule);
    //     $this->assertEquals(100, $schedule->amount);
    // }

    /** @test */
    public function it_shows_transactions_for_user_role()
    {
        $user = User::factory()->create(['role_id' => 2]);
        $this->actingAs($user);

        $account = Account::factory()->create();
        $txn = Transaction::factory()->create([
            'account_id' => $account->id,
            'role_id' => 2,
            'status' => 'pending',
        ]);

        $result = $this->repo->showTransactions();

        $this->assertCount(1, $result);
        $this->assertEquals($account->number, $result[0]['account_number']);
    }

    /** @test */
    public function it_returns_correct_strategy()
    {
        $this->assertInstanceOf(\App\Banking\Transactions\Strategies\DepositStrategy::class, $this->repo->strategyFor('deposit'));
        $this->assertInstanceOf(\App\Banking\Transactions\Strategies\WithdrawStrategy::class, $this->repo->strategyFor('withdraw'));
        $this->assertInstanceOf(\App\Banking\Transactions\Strategies\TransferStrategy::class, $this->repo->strategyFor('transfer'));
    }

    /** @test */
    public function it_gets_last_n_transactions_by_account()
    {
        $account = Account::factory()->create();
        Transaction::factory()->count(3)->create([
            'account_id' => $account->id,
            'status' => 'succeeded',
            'description' => 'Restaurant dinner',
            'type' => 'debit',
        ]);

        $result = $this->repo->lastNByAccount($account->id, 2);

        $this->assertCount(2, $result);
        $this->assertEquals('food', $result[0]['category']);
    }

    /** @test */
    public function it_gets_monthly_spending_summary()
    {
        $account = Account::factory()->create();
        Transaction::factory()->create([
            'account_id' => $account->id,
            'status' => 'succeeded',
            'type' => 'debit',
            'amount' => 50,
            'created_at' => Carbon::now()->subMonth(),
        ]);

        $result = $this->repo->monthlySpendingSummaryByAccount($account->id, 2);

        $this->assertNotEmpty($result);
        $this->assertTrue(array_sum($result) >= 50);
    }

    /** @test */
    public function it_gets_category_spending_summary()
    {
        $account = Account::factory()->create();
        Transaction::factory()->create([
            'account_id' => $account->id,
            'status' => 'succeeded',
            'type' => 'debit',
            'description' => 'Internet bill',
        ]);

        $result = $this->repo->categorySpendingByAccount($account->id, 3);

        $this->assertEquals('bills', $result[0]['category']);
    }

    /** @test */
    public function it_gets_recurring_merchants()
    {
        $account = Account::factory()->create();
        Transaction::factory()->count(2)->create([
            'account_id' => $account->id,
            'status' => 'succeeded',
            'description' => 'Uber ride',
        ]);

        $result = $this->repo->recurringMerchantsByAccount($account->id, 6, 2);

        $this->assertEquals('Uber', $result[0]['merchant']);
        $this->assertEquals(2, $result[0]['times']);
    }

    /** @test */
    public function it_gets_large_transactions()
    {
        $account = Account::factory()->create();
        Transaction::factory()->create([
            'account_id' => $account->id,
            'status' => 'succeeded',
            'type' => 'debit',
            'amount' => 500,
            'description' => 'Shopping mall',
        ]);

        $result = $this->repo->largeTransactionsByAccount($account->id, 3, 100);

        $this->assertCount(1, $result);
        $this->assertEquals('Shopping', $result[0]['merchant']);
    }

    /** @test */
    public function it_gets_all_transactions_for_admin_roles()
    {
        $user = User::factory()->create(['role_id' => 1]);
        $this->actingAs($user);

        Transaction::factory()->create();

        $result = $this->repo->allTransactions();

        $this->assertCount(1, $result);
    }

    /** @test */
    public function it_gets_all_transactions_for_customer_role()
    {
        $user = User::factory()->create(['role_id' => 6]);
        $this->actingAs($user);

        $account = Account::factory()->create(['customer_id' => $user->id]);
        Transaction::factory()->create(['account_id' => $account->id]);

        $result = $this->repo->allTransactions();

        $this->assertCount(1, $result);
    }

    /** @test */
    public function it_returns_empty_collection_for_other_roles()
    {
        $user = User::factory()->create(['role_id' => 99]);
        $this->actingAs($user);

        $result = $this->repo->allTransactions();

        $this->assertCount(0, $result);
    }
}