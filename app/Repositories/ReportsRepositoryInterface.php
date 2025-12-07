<?php

namespace App\Repositories;

interface ReportsRepositoryInterface
{
  public function getTransactionsDaily(): array;
  public function getTransactionsWeekly(): array;
  public function getTransactionsMonthly(): array;
  public function getAccountSummaries(): array;
}
