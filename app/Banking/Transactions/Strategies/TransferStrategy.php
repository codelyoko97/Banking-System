<?php

namespace App\Banking\Transactions\Strategies;

use App\Banking\Transactions\States\AccountStateFactory;
use App\DTO\ProcessTransactionDTO;
use App\Models\{Account, Transaction, Log};
use Illuminate\Support\Facades\DB;
use DomainException;

class TransferStrategy implements TransactionStrategy
{
  public function execute(ProcessTransactionDTO $dto, $id): Transaction
  {
    return DB::transaction(function () use ($dto, $id) {
      $src = Account::lockForUpdate()
        ->where('number', $dto->account_id)
        ->firstOrFail();

      if ($dto->amount > $src->balance) {
        throw new DomainException("Account doesn't have this amount");
      }

      $dst = Account::lockForUpdate()
        ->where('number', $dto->account_related_id)
        ->firstOrFail();
      if (!$dst) {
        throw new DomainException('Destination account required');
      }

      $txn = Transaction::create([
        'account_id' => $src->id,
        'type' => 'transfer',
        'status' => $id != null ? 'pending' : 'completed',
        'amount' => $dto->amount,
        'account_related_id' => $dst->id,
        'role_id' => $id,
        'employee_name' => $dto->employee_name ?? null,
        'description' => $dto->description ?? null,
      ]);

      if ($id != null) {
        return $txn->fresh();
      }

      $srcState = AccountStateFactory::make($src);
      $dstState = AccountStateFactory::make($dst);

      $ok1 = $srcState->withdraw($src, (float) $dto->amount);
      if (!$ok1) throw new DomainException('Transfer debit failed');

      $ok2 = $dstState->deposit($dst, (float) $dto->amount);
      if (!$ok2) throw new DomainException('Transfer credit failed');

      Log::create([
        'user_id' => $src->customer_id,
        'action' => 'transfer',
        'description' => "Transfer {$dto->amount} from {$src->number} to {$dst->number} via strategy"
      ]);

      return $txn->fresh();
    });
  }

  public function executeFromTransaction(Transaction $transaction, Account $account)
  {
    return DB::transaction(function () use ($account, $transaction) {
      $state = AccountStateFactory::make($account);
      $account2 = Account::find($transaction->account_related_id);
      if (!$account2) {
        throw new DomainException('Destination account required');
      }
      if ($account->balance < $transaction->amount) {
        return false;
      }
      $state->withdraw($account, (float) $transaction->amount);
      $state->deposit($account2, (float) $transaction->amount);

      $transaction->update(['status' => 'completed']);
      return true;
    });
  }
}
