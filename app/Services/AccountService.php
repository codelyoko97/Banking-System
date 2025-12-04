<?php

namespace App\Services;

use App\Repositories\AccountRepositoryInterface;
use App\Banking\Accounts\AccountFactory;
use App\Banking\Transactions\States\AccountStateFactory as StatesAccountStateFactory;
use App\Models\Account;
use App\Models\Log;
use App\Models\Status;
use App\Models\Type;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class AccountService
{
  protected AccountRepositoryInterface $repo;

  public function __construct(AccountRepositoryInterface $repo)
  {
    $this->repo = $repo;
  }

  public function create(array $data): Account
  {

    if (isset($data['type_name'])) {
      $type = Type::where('name', $data['type_name'])->firstOrFail();
      $data['type_id'] = $type->id;
    }
    $data = Arr::only($data, ['customer_id', 'type_id', 'name', 'account_related_id', 'balance', 'status_id', 'number']);

    if (!isset($data['status_id'])) {
      $active = Status::where('name', 'active')->first();
      $data['status_id'] = $active ? $active->id : null;
    }

    if (!isset($data['number'])) {
      $data['number'] = $this->generateAccountNumber();
    }
    $acc = $this->repo->create($data);
    Log::create([
      'user_id' => $acc->customer_id,
      'action' => 'create_account',
      'description' => "Account {$acc->number} created (type id {$acc->type_id})"
    ]);
    return $acc;
  }

  public function update(int $id, array $data): Account
  {
    $acc = $this->repo->find($id);
    if (!$acc) throw new \Exception('Account not found');
    $up = Arr::only($data, ['account_related_id']);
    $acc = $this->repo->update($acc, $up);
    Log::create([
      'user_id' => $acc->customer_id,
      'action' => 'update_account',
      'description' => "Account {$acc->number} updated"
    ]);
    return $acc;
  }

  public function close(int $id): Account
  {
    $acc = $this->repo->find($id);
    if (!$acc) throw new \Exception('Account not found');
    $state = StatesAccountStateFactory::make($acc);
    DB::transaction(function () use ($state, $acc) {
      $state->close($acc);
    });
    return $acc->fresh();
  }

  // optional helper: get balance via composite
  // public function getBalanceComposite(int $id): float
  // {
  //   $model = $this->repo->findWithChildren($id);
  //   $component = AccountFactory::buildFromModel($model, $this->repo);
  //   return $component->getBalance();
  // }

  // public function getBalanceComposite(int $id): float
  // {
  //   $model = $this->repo->getFullTree($id);
  //   if (!$model) {
  //     throw new \Exception("Account not found");
  //   }

  //   $component = AccountFactory::buildTree($model, $this->repo);
  //   return $component->getBalance();
  // }


  protected function generateAccountNumber(): string
  {
    do {
      $num = 'AC' . time() . rand(1000, 9999);
    } while (Account::where('number', $num)->exists());
    return $num;
  }

  public function getBalanceRecursive(int $accountId): float
  {
    $model = $this->repo->getFullTree($accountId);
    if (!$model) throw new \Exception("Account not found");

    $component = AccountFactory::buildTree($model, $this->repo);

    return $component->getBalance();
  }


  public function getAccountTreeStructured(int $accountId)
  {
    $root = $this->repo->getFullTree($accountId);
    if (!$root) throw new \Exception("Account not found");

    return $this->formatAccountNode($root);
  }

  private function formatAccountNode(Account $account)
  {
    return [
      'id' => $account->id,
      'number' => $account->number,
      'type' => $account->type->name ?? null,
      'status' => $account->status->name ?? null,
      'balance' => (float)$account->balance,
      'children' => $account->children->map(function ($child) {
        return $this->formatAccountNode($child);
      })
    ];
  }


  public function listAccountsForUser($user)
  {
    if ($user->role_id == 6) { 
      return $this->repo->listByCustomer($user->id);
    }

    return $this->repo->all();
  }
}
