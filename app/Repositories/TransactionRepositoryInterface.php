<?php

namespace App\Repositories;

use App\Models\Transaction;


interface TransactionRepositoryInterface
{
  public function find(int $id): ?Transaction;
  public function approve(int $id);
  public function createSchedule(array $data);
  public function lastNByAccount(int $accountId, int $n = 50): \Illuminate\Support\Collection;
  public function monthlySpendingSummaryByAccount(int $accountId, int $months = 6): array;
  public function categorySpendingByAccount(int $accountId, int $months = 3): array;
  public function recurringMerchantsByAccount(int $accountId, int $months = 6, int $minTimes = 2): array;
  public function largeTransactionsByAccount(int $accountId, int $months = 3, float $threshold = null): \Illuminate\Support\Collection;
}
