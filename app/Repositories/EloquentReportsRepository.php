<?php

namespace App\Repositories;

use App\Models\Account;
use App\Models\Transaction;
use Carbon\Carbon;

class EloquentReportsRepository implements ReportsRepositoryInterface
{
  public function getTransactionsDaily(): array
  {
    return Transaction::whereDate('created_at', Carbon::today())
      ->orderByDesc('created_at')
      ->get()
      ->toArray();
  }

  public function getTransactionsWeekly(): array
  {
    return Transaction::where('created_at', '>=', Carbon::now()->subDays(7))
      ->orderByDesc('created_at')
      ->get()
      ->toArray();
  }

  public function getTransactionsMonthly(): array
  {
    return Transaction::where('created_at', '>=', Carbon::now()->subDays(30))
      ->orderByDesc('created_at')
      ->get()
      ->toArray();
  }
  public function getAccountSummaries(): array
  {
    return Account::with(['type', 'status', 'customer'])
      ->get()
      ->map(function ($account) {
        return [
          'account_id' => $account->id,
          'account_number' => $account->number,
          'type' => $account->type?->name,
          'status' => $account->status?->name,
          'balance' => (float)$account->balance,

          'customer' => [
            'id' => $account->customer->id,
            'name' => $account->customer->name,
            'email' => $account->customer->email,
            'phone' => $account->customer->phone,
          ],

          'transactions_count' => Transaction::where('account_id', $account->id)->count(),

          'latest_transactions' => Transaction::where('account_id', $account->id)
            ->latest()
            ->limit(5)
            ->get(['id', 'amount', 'type', 'status', 'created_at'])
            ->toArray(),
        ];
      })
      ->toArray();
  }
}
