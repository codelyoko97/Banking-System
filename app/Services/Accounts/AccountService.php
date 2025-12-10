<?php

namespace App\Services\Accounts;

use App\DTO\AccountDTO;
use App\Repositories\AccountRepositoryInterface;
use App\Banking\Accounts\AccountFactory;
use App\Banking\Transactions\States\AccountStateFactory as StatesAccountStateFactory;
use App\Models\Account;
use App\Models\Log;
use App\Models\Status;
use App\Models\Type;
use App\Services\Accounts\Features\AccountInsurance;
use App\Services\Accounts\Features\BaseAccount;
use App\Services\Accounts\Features\OverdraftProtection;
use App\Services\Accounts\Features\PremiumService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
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

    Cache::forget("accounts:list:all");
    if (isset($data['customer_id'])) {
      Cache::forget("accounts:list:user:{$data['customer_id']}");
    }

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

  // public function getBalanceRecursive(int $accountId): float
  // {
  //   $model = $this->repo->getFullTree($accountId);
  //   if (!$model) throw new \Exception("Account not found");

  //   $component = AccountFactory::buildTree($model, $this->repo);

  //   return $component->getBalance();
  // }



  public function getBalanceRecursive(int $accountId): float
  {
    return Cache::remember("account:{$accountId}:balance", 300, function () use ($accountId) {
      $model = $this->repo->getFullTree($accountId);
      if (!$model) throw new \Exception("Account not found");

      $component = AccountFactory::buildTree($model, $this->repo);
      return $component->getBalance();
    });
  }



  // public function getAccountTreeStructured(int $accountId)
  // {
  //   $root = $this->repo->getFullTree($accountId);
  //   if (!$root) throw new \Exception("Account not found");

  //   return $this->formatAccountNode($root);
  // }


  public function getAccountTreeStructured(int $accountId)
  {
    return Cache::remember("account:{$accountId}:fulltree", 300, function () use ($accountId) {
      $root = $this->repo->getFullTree($accountId);
      if (!$root) throw new \Exception("Account not found");

      return $this->formatAccountNode($root);
    });
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


  // public function listAccountsForUser($user)
  // {
  //   if ($user->role_id == 6) {
  //     return $this->repo->listByCustomer($user->id);
  //   }

  //   return $this->repo->all();
  // }


  public function listAccountsForUser($user)
  {
    if ($user->role_id == 6) {
      return Cache::remember("accounts:list:user:{$user->id}", 300, function () use ($user) {
        return $this->repo->listByCustomer($user->id);
      });
    }

    return Cache::remember("accounts:list:all", 300, function () {
      return $this->repo->all();
    });
  }


  // public function filterByStatus(?string $status)
  // {
  //   return $this->repo->filterByStatus($status);
  // }


  public function filterByStatus(?string $status)
  {
    $key = "accounts:filter:status:" . ($status ?? "all");

    return Cache::remember($key, 300, function () use ($status) {
      return $this->repo->filterByStatus($status);
    });
  }


  public function changeStatus(int $accountId, string $statusName)
  {
    $acc = $this->repo->find($accountId);
    if (!$acc) {
      throw new \Exception("Account not found");
    }

    $status = Status::where('name', $statusName)->first();
    if (!$status) {
      throw new \Exception("Invalid status name");
    }

    $acc = $this->repo->setStatus($acc, $status->id);

    Log::create([
      'user_id' => auth()->id(),
      'action' => 'change_account_status',
      'description' => "Account {$acc->number} status changed to {$statusName}"
    ]);

    Cache::forget("accounts:filter:status:active");
    Cache::forget("accounts:filter:status:inactive");
    Cache::forget("account:{$acc->id}:fulltree");
    Cache::forget("account:{$acc->id}:balance");

    return $acc;
  }

  // decorator
  public function getDecoratedAccount(int $id): AccountDTO
  {
    $raw = $this->repo->getAccountById($id);

    // الحساب الأساسي
    $account = new BaseAccount($raw->owner_name, $raw->balance);

    // جلب الميزات من DB
    $features = $this->repo->getFeatures($id);

    // تطبيق الميزات حسب الجدول
    foreach ($features as $f) {
      if ($f === "overdraft") {
        $account = new OverdraftProtection($account);
      }
      if ($f === "insurance") {
        $account = new AccountInsurance($account);
      }
      if ($f === "premium") {
        $account = new PremiumService($account);
      }
    }

    return new AccountDTO(
      $id,
      $account->getDescription(),
      $account->getBalance()
    );
  }
  public function filterByStatusWithFeatures(?string $status)
  {
    $accounts = $this->repo->filterByStatus($status);

    return $accounts->map(function ($acc) {
      $features = $this->repo->getFeatures($acc->id);
      return [
        'id' => $acc->id,
        'number' => $acc->number,
        'type' => $acc->type->name ?? null,
        'status' => $acc->status->name ?? null,
        'balance' => (float)$acc->balance,
        'features' => $features,
      ];
    });
  }
}
