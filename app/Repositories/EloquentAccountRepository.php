<?php

namespace App\Repositories;

use App\Models\Account;
use Illuminate\Support\Facades\DB;

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


  public function all()
  {
    return Account::with(['type', 'status', 'children'])->get();
  }

  public function listByCustomer(int $customerId)
  {
    return Account::where('customer_id', $customerId)
      ->with(['type', 'status', 'children'])
      ->get();
  }

  public function filterByStatus(?string $status)
  {
    $q = Account::with(['type', 'status', 'children']);

    if ($status) {
      $q->whereHas('status', function ($s) use ($status) {
        $s->where('name', $status);
      });
    }

    return $q->orderByDesc('created_at')->get();
  }

  // decorator
  public function getAccountById(int $id)
  {
    return Account::findOrFail($id);
  }

  public function getFeatures(int $accountId): array
  {
    return DB::table('account_features')
      ->where('account_id', $accountId)
      ->pluck('feature')
      ->toArray();
  }
}
