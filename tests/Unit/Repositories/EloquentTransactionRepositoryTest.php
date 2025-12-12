<?php

namespace Tests\Unit\Repositories;

use App\Models\User;
use App\Models\Account;
use App\Models\Transaction;
use App\Repositories\EloquentTransactionRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EloquentTransactionRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected EloquentTransactionRepository $repo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = new EloquentTransactionRepository();
    }

    /** ✅ اختبار find */
    public function test_find_transaction_by_id()
    {
        $account = Account::factory()->create();
        $txn = Transaction::factory()->create([
            'account_id' => $account->id,
            'status' => 'pending',
            'type' => 'deposit',
        ]);

        $found = $this->repo->find($txn->id);

        $this->assertNotNull($found);
        $this->assertEquals($txn->id, $found->id);
    }

    /** ✅ اختبار reject */
    public function test_reject_transaction_successfully()
    {
        $user = User::factory()->create(['role_id' => 1]);
        $this->actingAs($user);

        $account = Account::factory()->create(['customer_id' => $user->id]);
        $txn = Transaction::factory()->create([
            'account_id' => $account->id,
            'role_id' => 1,
            'status' => 'pending',
            'type' => 'withdraw',
        ]);

        $result = $this->repo->reject($txn->id);

        $this->assertTrue($result['success']);
        $this->assertEquals('Transaction rejected successfully', $result['message']);
        $this->assertEquals('rejected', $txn->fresh()->status);
    }

    /** ✅ اختبار approve مع صلاحيات خاطئة */
    public function test_approve_transaction_unauthorized()
    {
        $user = User::factory()->create(['role_id' => 2]);
        $this->actingAs($user);

        $account = Account::factory()->create();
        $txn = Transaction::factory()->create([
            'account_id' => $account->id,
            'role_id' => 1,
            'status' => 'pending',
            'type' => 'deposit',
        ]);

        $result = $this->repo->approve($txn->id);

        $this->assertFalse($result['status']);
        $this->assertEquals('Unauthorized to do this job', $result['message']);
    }

    /** ✅ اختبار showTransactions */
    public function test_show_transactions_returns_pending_for_user_role()
    {
        $user = User::factory()->create(['role_id' => 1]);
        $this->actingAs($user);

        $account = Account::factory()->create();
        Transaction::factory()->create([
            'account_id' => $account->id,
            'role_id' => 1,
            'status' => 'pending',
            'type' => 'deposit',
            'description' => 'Test transaction',
        ]);

        $transactions = $this->repo->showTransactions();

        $this->assertCount(1, $transactions);
        $this->assertEquals('pending', $transactions->first()->status);
        $this->assertArrayHasKey('account_number', $transactions->first()->toArray());
    }
}