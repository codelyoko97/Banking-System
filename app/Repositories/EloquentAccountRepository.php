<?php

namespace App\Repositories;

use App\Models\Account;

class EloquentAccountRepository implements AccountRepositoryInterface
{
  public function find(int $id): ?Account
  {
    return Account::find($id);
  }

  public function findWithChildren(int $id): ?Account
  {
    return Account::with('children')->find($id);
  }

  public function findWithNumber(int $num): Account
  {
    return Account::where('number', $num)->first();
  }


  public function create(array $data): Account
  {
    return Account::create($data);
  }

  public function update(Account $account, array $data): Account
  {
    $account->update($data);
    return $account->fresh();
  }

  public function setStatus(Account $account, int $statusId): Account
  {
    $account->status_id = $statusId;
    $account->save();
    return $account->fresh();
  }

  public function adjustBalance(Account $account, float $delta): Account
  {
    $new = bcadd((string)$account->balance, (string)$delta, 4);
    $account->balance = $new;
    $account->save();
    return $account->fresh();
  }

  public function getFullTree(int $rootId): ?Account
  {
    $root = Account::find($rootId);
    if (!$root) return null;

    $this->loadChildrenRecursive($root);
    return $root;
  }

  private function loadChildrenRecursive(Account $account)
  {
    $account->setRelation('children', $account->children()->get());

    foreach ($account->children as $child) {
      $this->loadChildrenRecursive($child);
    }
  }
}
