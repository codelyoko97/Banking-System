<?php

namespace App\Repositories;

use App\Banking\Transactions\States\AccountStateFactory;
use App\Banking\Transactions\Strategies\DepositStrategy;
use App\Banking\Transactions\Strategies\TransactionStrategy;
use App\Banking\Transactions\Strategies\TransferStrategy;
use App\Banking\Transactions\Strategies\WithdrawStrategy;
use App\Models\Account;
use App\Models\Log;
use App\Models\Notification;
use App\Models\SchedualeTransaction;
use App\Models\Transaction;
use DomainException;
use Illuminate\Support\Facades\Auth;

class EloquentTransactionRepository implements TransactionRepositoryInterface
{

  public function find(int $id): ?Transaction
  {
    return Transaction::find($id);
  }

  public function approve(int $id)
  {
    $user = Auth::user();
    $transaction = Transaction::with('account')->findOrFail($id);
    $account = $transaction->account;

    if ($user->role_id != $transaction->role_id) {
      return [
        'status' => false,
        'message' => 'Unauthorized to do this job'
      ];
    }

    $strategy = $this->strategyFor($transaction->type);
    $status = $strategy->executeFromTransaction($transaction, $account);
    if ($status == false) {
      $transaction->update(['status' => 'failed']);
      Notification::query()->create([
        'user_id' => $account->customer_id,
        'content' => "Failed to withdraw {$transaction['amount']} because your balance is not enough",
        'type' => 'Failed Approvment'
      ]);
      return [
        'status' => false,
        'message' => 'Transaction Failed',
      ];
    }

    Log::create([
      'user_id' => $user->id,
      'action' => $transaction->type,
      'description' => "User {$user['name']} approved transaction {$transaction['id']}"
    ]);
    return [
      'status' => true,
      'message' => 'Transaction approved successfully',
      'transaction' => $transaction->fresh()
    ];
  }

  public function createSchedule(array $data)
  {
    $data['account_id'] = Account::where('number', $data['account_id'])->first()->id;
    $data['account_related_id'] = Account::where('number', $data['account_related_id'])->first()->id ?? null;
    return SchedualeTransaction::create($data);
  }


  public function strategyFor(string $type): TransactionStrategy
  {
    return match ($type) {
      'deposit' => new DepositStrategy(),
      'withdraw' => new WithdrawStrategy(),
      'transfer' => new TransferStrategy(),
      default => throw new DomainException('Unsupported transaction type')
    };
  }
}
