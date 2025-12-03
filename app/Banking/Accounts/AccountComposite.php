<?php

namespace App\Banking\Accounts;

class AccountComposite implements AccountComponent
{
  protected int $id;
  protected array $children = [];

  public function __construct(int $id)
  {
    $this->id = $id;
  }

  public function addChild(AccountComponent $c)
  {
    $this->children[] = $c;
  }

  public function getChildren(): array
  {
    return $this->children;
  }

  public function getId(): int
  {
    return $this->id;
  }

  public function getBalance(): float
  {
    $sum = '0.0000';
    foreach ($this->children as $c) {
      $sum = bcadd($sum, (string)$c->getBalance(), 4);
    }
    return (float)$sum;
  }

  public function deposit(float $amount, array $meta = []): bool
  {
    throw new \Exception('Cannot deposit directly to composite account');
  }

  public function withdraw(float $amount, array $meta = []): bool
  {
    throw new \Exception('Cannot withdraw directly from composite account');
  }
}
