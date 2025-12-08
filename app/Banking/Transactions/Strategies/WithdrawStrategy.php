<?php

namespace App\Banking\Transactions\Strategies;

use App\DTO\ProcessTransactionDTO;
use App\Models\{Account, Transaction, Log, Notification};
use App\Banking\Transactions\States\AccountStateFactory;
use App\Events\TransactionApproved;
use App\Events\TransactionCreated;
use App\Jobs\LogJob;
use Illuminate\Support\Facades\DB;
use DomainException;
use Illuminate\Support\Facades\Cache;

class WithdrawStrategy implements TransactionStrategy
{
  public function execute(ProcessTransactionDTO $dto, ?int $id): Transaction
  {
    return DB::transaction(function () use ($dto, $id) {
      $account = Account::lockForUpdate()
        ->where('number', $dto->account_id)
        ->firstOrFail();

      if ($dto->amount > $account->balance) {
        throw new DomainException("Account doesn't have this amount");
      }

      $txn = Transaction::create([
        'account_id' => $account->id,
        'type' => 'withdraw',
        'status' => $id != null ? 'pending' : 'completed',
        'amount' => $dto->amount,
        'account_related_id' => $dto->account_related_id ?? null,
        'role_id' => $id,
        'employee_name' => $dto->employee_name ?? null,
        'description' => $dto->description ?? null,
      ]);

      if ($id != null) {
        $txn['user_id'] = $id;
        event(new TransactionCreated($txn));
        return $txn->fresh();
      }

      $state = AccountStateFactory::make($account);
      $ok = $state->withdraw($account, (float) $dto->amount);
      if (!$ok) {
        throw new DomainException('Withdraw failed');
      }

      LogJob::dispatch($account->customer_id, 'withdraw', "Withdraw {$dto->amount} from account {$account->number}");
      Log::create([
        'user_id' => $account->customer_id,
        'action' => 'withdraw',
        'description' => "Withdraw {$dto->amount} from account {$account->number} via strategy"
      ]);
      Cache::forget("account:{$account->id}:fulltree");
      Cache::forget("account:{$account->id}:children");
      Cache::forget("accounts:list:user:{$account->customer_id}");
      Cache::forget("account:{$account->id}:balance");

      return $txn->fresh();
    });
  }

  public function executeFromTransaction(Transaction $transaction, Account $account)
  {
    return DB::transaction(function () use ($account, $transaction) {
      $state = AccountStateFactory::make($account);
      if ($account->balance < $transaction->amount) {
        return false;
      }
      $state->withdraw($account, (float) $transaction->amount);
      $transaction->update(['status' => 'completed']);
      $transaction['user_id'] = $account->customer->id;
      event(new TransactionApproved($transaction));
      return true;
    });
  }
}
