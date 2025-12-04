<?php

namespace App\Repositories;

use App\Models\Transaction;

interface TransactionRepositoryInterface
{
  public function find(int $id): ?Transaction;
  public function approve(int $id);
  public function createSchedule(array $data);
}
