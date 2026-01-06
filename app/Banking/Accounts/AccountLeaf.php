<?php

namespace App\Banking\Accounts;

use App\Models\Account;
use App\Repositories\AccountRepositoryInterface;
use Illuminate\Support\Facades\DB;

class AccountLeaf implements AccountComponent
{
  protected Account $model;
  protected AccountRepositoryInterface $repo;

  public function __construct(Account $model, AccountRepositoryInterface $repo)
  {
    $this->model = $model;
    $this->repo = $repo;
  }

  public function getId(): int
  {
    return $this->model->id;
  }

  public function getChildren(): array
  {
    return [];
  }

  public function getBalance(): float
  {
    return (float)$this->model->balance;
  }

  public function deposit(float $amount, array $meta = []): bool
  {
    return DB::transaction(function () use ($amount) {
      $this->repo->adjustBalance($this->model, $amount);
      \App\Models\Log::create([
        'user_id' => $this->model->customer_id,
        'action' => 'deposit',
        'description' => "Deposit {$amount} to account {$this->model->number}"
      ]);
      return true;
    });
  }

  public function withdraw(float $amount, array $meta = []): bool
  {
    return DB::transaction(function () use ($amount) {
      if (bccomp((string)$this->model->balance, (string)$amount, 4) < 0) {
        throw new \Exception('Insufficient funds');
      }
      $this->repo->adjustBalance($this->model, -$amount);
      \App\Models\Log::create([
        'user_id' => $this->model->customer_id,
        'action' => 'withdraw',
        'description' => "Withdraw {$amount} from account {$this->model->number}"
      ]);
      return true;
    });
  }
}
