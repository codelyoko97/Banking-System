<?php
namespace Tests\Feature;

use Tests\TestCase;
use App\Services\TransactionService;
use App\Repositories\TransactionRepositoryInterface;
use App\DTO\ProcessTransactionDTO;
use App\Models\Account;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class TransactionServiceTest extends TestCase
{
    protected $transactionRepo;
    protected $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->transactionRepo = $this->createMock(TransactionRepositoryInterface::class);
        $this->service = new TransactionService($this->transactionRepo);
    }

    /** @test */
    public function it_processes_transaction_through_approval_chain()
    {
        $user = User::factory()->create();
        $account = Account::factory()->create([
            'customer_id' => $user->id
        ]);

        Log::info('Response JSON: ' . $account->toJson());

        $dto = new ProcessTransactionDTO(
            account_id: $account->number,
            amount: 1000.0,
            type: 'deposit',
            account_related_id: null,
            description: 'Initial deposit',
            employee_name: 'Teller John',
            requestedBy: $user
        );

        $result = $this->service->process($dto);

        $this->assertNotNull($result);
    }
}