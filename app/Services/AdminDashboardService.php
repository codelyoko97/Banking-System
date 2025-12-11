<?php

namespace App\Services;

use App\DTO\Dashboard\AccountsMonthlyDTO;
use App\DTO\Dashboard\LogDTO;
use App\DTO\Dashboard\StatusCountDTO;
use App\DTO\Dashboard\TopCustomersDTO;
use App\DTO\Dashboard\Transaction24hDTO;
use App\DTO\Dashboard\WeeklyTransactionsDTO;
use App\Repositories\DashboardRepositoryInterface;
use Illuminate\Support\Facades\Cache;

class AdminDashboardService
{
  public function __construct(
    protected DashboardRepositoryInterface $repo
  ) {}

  // -------------------- Charts --------------------

  public function transactionsWeekly(): array
  {
    // return $this->repo->getWeeklyTransactions();
    return Cache::remember('dashboard.weekly_transactions', 60, function () {
      $rows = $this->repo->getWeeklyTransactions();
      return (new WeeklyTransactionsDTO($rows))->data;
    });
  }

  public function transactionStatusCounts(): array
  {
    // return $this->repo->getTransactionStatusCounts();
    return Cache::remember('dashboard.status_counts', 60, function () {
      $rows = $this->repo->getTransactionStatusCounts();
      return (new StatusCountDTO($rows))->data;
    });
  }

  public function accountsMonthly(int $days = 30): array
  {
    // return $this->repo->getAccountsMonthly($days);
    return Cache::remember('dashboard.accounts_monthly', 60, function () {
      $rows = $this->repo->getAccountsMonthly();
      return (new AccountsMonthlyDTO($rows))->data;
    });
  }

  public function topCustomers(int $limit = 10): array
  {
    // return $this->repo->getTopCustomers($limit);
    return Cache::remember('dashboard.top_customers', 60, function () {
      $rows = $this->repo->getTopCustomers();
      return (new TopCustomersDTO($rows))->data;
    });
  }


  // -------------------- Stats --------------------

  public function accountsToday(): array
  {
    return [
      'count' => $this->repo->getAccountsToday()
    ];
  }

  // public function transactions24h(): array
  // {
  //   return [
  //     'count' => $this->repo->getTransactions24h()
  //   ];
  // }

  public function transactions24h(): array
  {
    $row = $this->repo->getTransactions24h();
    return Transaction24hDTO::build($row);
  }


  // -------------------- Users --------------------

  public function getAllCustomers(): array
  {
    return $this->repo->getAllCustomers();
  }

  public function getAllEmployees(): array
  {
    return $this->repo->getAllEmployees();
  }

  // -------------------- Logs --------------------

  // public function getLatestLogs(int $limit = 20): array
  // {
  //   return $this->repo->getLatestLogs($limit);
  // }

  public function logs(array $filters, int $perPage = 20)
  {
    $paginated = $this->repo->getLogs($filters, $perPage);

    return [
      'data' => array_map(
        fn($log) => LogDTO::transform($log),
        $paginated->items()
      ),
      'pagination' => [
        'current_page' => $paginated->currentPage(),
        'last_page'    => $paginated->lastPage(),
        'per_page'     => $paginated->perPage(),
        'total'        => $paginated->total(),
      ]
    ];
  }

  public function exportLogs(array $filters)
  {
    $rows = $this->repo->exportLogs($filters);

    $csv = "ID,User,Email,Action,Description,Date\n";

    foreach ($rows as $r) {
      $csv .= "{$r['id']},{$r['user']['name']},{$r['user']['email']},{$r['action']},\"{$r['description']}\",{$r['created_at']}\n";
    }

    return $csv;
  }
}


// <?php
// namespace App\Services;

// use App\DTO\Dashboard\AccountsMonthlyDTO;
// use App\DTO\Dashboard\LogDTO;
// use App\DTO\Dashboard\StatusCountDTO;
// use App\DTO\Dashboard\TopCustomersDTO;
// use App\DTO\Dashboard\Transaction24hDTO;
// use App\DTO\Dashboard\WeeklyTransactionsDTO;
// use App\Repositories\DashboardRepositoryInterface;
// use Illuminate\Support\Facades\Cache;

// class AdminDashboardService
// {
//     public function __construct(
//         protected DashboardRepositoryInterface $repo
//     ) {}

//     // -------------------- Charts --------------------

//     public function transactionsWeekly(): array
//     {
//         return Cache::remember('dashboard.weekly_transactions', 60, function () {
//             $rows = $this->repo->getWeeklyTransactions();
//             return (new WeeklyTransactionsDTO($rows))->data;
//         });
//     }

//     public function transactionStatusCounts(): array
//     {
//         return Cache::remember('dashboard.status_counts', 60, function () {
//             $rows = $this->repo->getTransactionStatusCounts();
//             return (new StatusCountDTO($rows))->data;
//         });
//     }

//     public function accountsMonthly(int $days = 30): array
//     {
//         return Cache::remember("dashboard.accounts_monthly_$days", 60, function () use ($days) {
//             $rows = $this->repo->getAccountsMonthly($days);
//             return (new AccountsMonthlyDTO($rows))->data;
//         });
//     }

//     public function topCustomers(int $limit = 10): array
//     {
//         return Cache::remember("dashboard.top_customers_$limit", 60, function () use ($limit) {
//             $rows = $this->repo->getTopCustomers($limit);
//             return (new TopCustomersDTO($rows))->data;
//         });
//     }

//     // -------------------- Stats --------------------

//     public function accountsToday(): array
//     {
//         return [
//             'count' => $this->repo->getAccountsToday()
//         ];
//     }

//     public function transactions24h(): array
//     {
//         $row = $this->repo->getTransactions24h();
//         return Transaction24hDTO::build($row);
//     }

//     // -------------------- Users --------------------

//     public function getAllCustomers(): array
//     {
//         return $this->repo->getAllCustomers();
//     }

//     public function getAllEmployees(): array
//     {
//         return $this->repo->getAllEmployees();
//     }

//     // -------------------- Logs --------------------

//     public function logs(array $filters, int $perPage = 20)
//     {
//         $paginated = $this->repo->getLogs($filters, $perPage);

//         return [
//             'data' => array_map(
//                 fn($log) => LogDTO::transform($log),
//                 $paginated->items()
//             ),
//             'pagination' => [
//                 'current_page' => $paginated->currentPage(),
//                 'last_page'    => $paginated->lastPage(),
//                 'per_page'     => $paginated->perPage(),
//                 'total'        => $paginated->total(),
//             ]
//         ];
//     }

//     public function exportLogs(array $filters)
//     {
//         $rows = $this->repo->exportLogs($filters);

//         $csv = "ID,User,Email,Action,Description,Date\n";

//         foreach ($rows as $r) {
//             $csv .= "{$r['id']},{$r['user']['name']},{$r['user']['email']},{$r['action']},\"{$r['description']}\",{$r['created_at']}\n";
//         }

//         return $csv;
//     }
// }