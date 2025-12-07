<?php

namespace App\Repositories;

use App\Models\Transaction;
use App\Models\Account;
use App\Models\Log;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardRepository implements DashboardRepositoryInterface
{
  // ------------------ Charts ------------------

  public function getWeeklyTransactions(): array
  {
    return Transaction::selectRaw('DATE(created_at) as day, COUNT(*) as count')
      ->where('created_at', '>=', now()->subDays(6)->startOfDay())
      ->groupBy('day')
      ->orderBy('day')
      ->get()
      ->toArray();
  }

  public function getTransactionStatusCounts(): array
  {
    return Transaction::selectRaw('status, COUNT(*) as count')
      ->groupBy('status')
      ->get()
      ->toArray();
  }

  public function getAccountsMonthly(int $days = 30): array
  {
    return Account::selectRaw('DATE(created_at) as date, COUNT(*) as count')
      ->where('created_at', '>=', now()->subDays($days - 1)->startOfDay())
      ->groupBy('date')
      ->orderBy('date')
      ->get()
      ->toArray();
  }

  public function getTopCustomers(int $limit = 10): array
  {
    return Transaction::selectRaw('accounts.customer_id as user_id, users.name, users.email, SUM(amount) as total_amount, COUNT(transactions.id) as tx_count')
      ->join('accounts', 'transactions.account_id', '=', 'accounts.id')
      ->join('users', 'accounts.customer_id', '=', 'users.id')
      ->where('transactions.status', 'succeeded')
      ->groupBy('accounts.customer_id', 'users.name', 'users.email')
      ->orderByDesc('total_amount')
      ->limit($limit)
      ->get()
      ->toArray();
  }


  // ------------------ Dashboard Counters ------------------

  public function getAccountsToday(): int
  {
    return Account::whereDate('created_at', today())->count();
  }

  public function getTransactions24h(): int
  {
    return Transaction::where('created_at', '>=', now()->subDay())->count();
  }

  public function getAllAccounts(): array
  {
    return Account::with(['type', 'status', 'customer'])->get()->toArray();
  }

  // ------------------ Users ------------------

  public function getAllCustomers(): array
  {
    return User::whereHas('role', fn($q) => $q->where('name', 'Customer'))
      ->get()
      ->toArray();
  }

  public function getAllEmployees(): array
  {
    return User::whereHas('role', fn($q) => $q->whereIn('name', ['Teller', 'Manager', 'Admin', 'Auditor', 'Support Agent']))
      ->get()
      ->toArray();
  }

  // ------------------ Logs ------------------

  // public function getLatestLogs(int $limit = 20): array
  // {
  //   return Log::orderBy('created_at', 'desc')
  //     ->limit($limit)
  //     ->get()
  //     ->toArray();
  // }

  public function getLogs(array $filters = [], int $perPage = 20)
  {
    $query = Log::query()->with('user:id,name,email');

    if (!empty($filters['user_id'])) {
      $query->where('user_id', $filters['user_id']);
    }

    if (!empty($filters['action'])) {
      $query->where('action', 'like', "%{$filters['action']}%");
    }

    if (!empty($filters['date_from'])) {
      $query->whereDate('created_at', '>=', $filters['date_from']);
    }

    if (!empty($filters['date_to'])) {
      $query->whereDate('created_at', '<=', $filters['date_to']);
    }

    return $query->orderByDesc('id')->paginate($perPage);
  }


  public function exportLogs(array $filters = []): array
  {
    $query = Log::query()->with('user:id,name,email');

    if (!empty($filters['user_id'])) {
      $query->where('user_id', $filters['user_id']);
    }

    if (!empty($filters['action'])) {
      $query->where('action', 'like', "%{$filters['action']}%");
    }

    if (!empty($filters['date_from'])) {
      $query->whereDate('created_at', '>=', $filters['date_from']);
    }

    if (!empty($filters['date_to'])) {
      $query->whereDate('created_at', '<=', $filters['date_to']);
    }

    return $query->orderByDesc('id')->get()->toArray();
  }
}
