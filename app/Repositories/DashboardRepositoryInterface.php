<?php

namespace App\Repositories;

interface DashboardRepositoryInterface
{
  // public function getWeeklyTransactions(): array;

  // public function getAccountsToday(): int;

  // public function getTransactions24h(): int;

  // public function getTransactionStatusCounts(): array;

  // public function getLatestLogs(int $limit = 20): array;

  // public function getAllCustomers(): array;

  // public function getAllEmployees(): array;

  // public function getAllAccounts(): array;

  // Charts
  public function getWeeklyTransactions(): array;
  public function getTransactionStatusCounts(): array;
  public function getAccountsMonthly(int $days = 30): array;
  public function getTopCustomers(int $limit = 10): array;

  // Dashboard counters
  public function getAccountsToday(): int;
  public function getTransactions24h(): array;
  public function getAllAccounts(): array;

  // Users
  public function getAllCustomers(): array;
  public function getAllEmployees(): array;

  // Logs
  // public function getLatestLogs(int $limit = 20): array;
  public function getLogs(array $filters = [], int $perPage = 20);
  public function exportLogs(array $filters = []): array;
}
