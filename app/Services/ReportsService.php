<?php

namespace App\Services;

use App\DTO\AccountSummaryDTO;
use App\DTO\TransactionReportDTO;
use App\Repositories\ReportsRepositoryInterface;
use Illuminate\Support\Facades\Cache;

class ReportsService
{
  public function __construct(private ReportsRepositoryInterface $repo) {}

  public function getByRange(string $range): TransactionReportDTO
  {
    return match ($range) {
      'daily'   => $this->daily(),
      'weekly'  => $this->weekly(),
      'monthly' => $this->monthly(),
      default   => $this->daily(),
    };
  }

  public function daily(): TransactionReportDTO
  {
    return Cache::remember('report.daily', 60, function () {
      return new TransactionReportDTO($this->repo->getTransactionsDaily());
    });
  }

  public function weekly(): TransactionReportDTO
  {
    return Cache::remember('report.weekly', 60, function () {
      return new TransactionReportDTO($this->repo->getTransactionsWeekly());
    });
  }

  public function monthly(): TransactionReportDTO
  {
    return Cache::remember('report.monthly', 60, function () {
      return new TransactionReportDTO($this->repo->getTransactionsMonthly());
    });
  }

  public function accountSummaries()
  {
    return Cache::remember('report.account_summaries', 60, function () {
      $rows = $this->repo->getAccountSummaries();
      return new AccountSummaryDTO($rows);
    });
  }
}
