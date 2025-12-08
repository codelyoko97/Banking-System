<?php

namespace App\Repositories;

use App\Models\Account;

interface AccountRepositoryInterface
{
  public function find(int $id): ?Account;
  public function findWithChildren(int $id): ?Account;
  public function findWithNumber(int $num): ?Account;
  public function create(array $data): Account;
  public function update(Account $account, array $data): Account;
  public function setStatus(Account $account, int $statusId): Account;
  public function adjustBalance(Account $account, float $delta): Account;
  public function getFullTree(int $id): ?Account;
  public function all();
  public function listByCustomer(int $customerId);
  public function filterByStatus(?string $status);

}
