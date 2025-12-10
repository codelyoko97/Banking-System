<?php

namespace App\Banking\Transactions\Strategies;

use App\Banking\Transactions\States\AccountStateFactory;
use App\DTO\ProcessTransactionDTO;
use App\Events\TransactionApproved;
use App\Events\TransactionCreated;
use App\Jobs\LogJob;
use App\Models\{Account, Transaction};
use Illuminate\Support\Facades\DB;
use DomainException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class DepositStrategy implements TransactionStrategy
{
  // public function execute(ProcessTransactionDTO $dto, ?int $id): Transaction
  // {
  //   return DB::transaction(function () use ($dto, $id) {
  //     $account = Account::lockForUpdate()
  //       ->where('number', $dto->account_id)
  //       ->firstOrFail();

  //     $txn = Transaction::create([
  //       'account_id' => $account->id,
  //       'type' => 'deposit',
  //       'status' => $id != null ? 'pending' : 'completed',
  //       'amount' => $dto->amount,
  //       'account_related_id' => null,
  //       'role_id' => $id,
  //       'employee_name' => $dto->employee_name ?? null,
  //       'description' => $dto->description ?? null,
  //     ]);

  //     if ($id != null) {
  //       $txn['user_id'] = $id;
  //       event(new TransactionCreated($txn));
  //       return $txn->fresh();
  //     }

  //     $state = AccountStateFactory::make($account);
  //     $ok = $state->deposit($account, (float) $dto->amount);
  //     if (!$ok) {
  //       throw new DomainException('Deposit failed');
  //     }
  //     LogJob::dispatch($account->customer_id, 'deposit', "Deposit {$dto->amount} to account {$account->number}");

  //     Cache::forget("account:{$account->id}:fulltree");
  //     Cache::forget("account:{$account->id}:children");
  //     Cache::forget("accounts:list:user:{$account->customer_id}");
  //     Cache::forget("account:{$account->id}:balance"); 


  //     return $txn->fresh();
  //   });
  // }





  public function execute(ProcessTransactionDTO $dto, ?int $id): Transaction
  {
    return DB::transaction(function () use ($dto, $id) {

      $account = Account::lockForUpdate()
        ->where('number', $dto->account_id)
        ->firstOrFail();
      Log::info("message");
      // ---------------------------------
      // 1) جلب الميزات (decorator features)
      // ---------------------------------
      $features = DB::table('account_features')
        ->where('account_id', $account->id)
        ->pluck('feature')
        ->toArray();

      // ---------------------------------------------------
      // 2) حساب المبلغ النهائي قبل الإيداع بناءً على الميزات
      // ---------------------------------------------------
      $finalAmount = $dto->amount;

      // Premium → bonus 1%
      if (in_array('premium', $features)) {
        $finalAmount += $dto->amount * 0.01;
      }

      // Insurance → خصم 0.5% على الإيداع
      if (in_array('insurance', $features)) {
        $finalAmount -= $dto->amount * 0.005;
      }

      // Overdraft → ما بيأثر على الإيداع، فنتجاهله بالإيداع فقط


      // ---------------------------------------------------
      // 3) إنشاء الترانزاكشن
      // ---------------------------------------------------
      $txn = Transaction::create([
        'account_id' => $account->id,
        'type' => 'deposit',
        'status' => $id != null ? 'pending' : 'completed',
        'amount' => $dto->amount,      // المبلغ الأصلي
        'account_related_id' => null,
        'role_id' => $id,
        'employee_name' => $dto->employee_name ?? null,
        'description' => "Deposit with features applied",
      ]);

      if ($id != null) {
        $txn['user_id'] = $id;
        event(new TransactionCreated($txn));
        return $txn->fresh();
      }

      $state = AccountStateFactory::make($account);
      $ok = $state->deposit($account, (float) $finalAmount);

      if (!$ok) {
        throw new DomainException('Deposit failed');
      }

      Cache::forget("account:{$account->id}:fulltree");
      Cache::forget("account:{$account->id}:children");
      Cache::forget("accounts:list:user:{$account->customer_id}");
      LogJob::dispatch(
        $account->customer_id,
        'deposit',
        "Deposit {$finalAmount} to account {$account->number}"
      );

      Cache::forget("account:{$account->id}:balance");

      return $txn->fresh();
    });
  }




  public function executeFromTransaction(Transaction $transaction, Account $account)
  {
    return DB::transaction(function () use ($account, $transaction) {
      $state = AccountStateFactory::make($account);
      $state->deposit($account, (float) $transaction->amount);
      $transaction->update(['status' => 'completed']);
      $transaction['user_id'] = $account->customer->id;
      event(new TransactionApproved($transaction));
      return true;
    });
  }
}
