<?php

namespace App\Banking\Transactions\Adapter;

interface PaymentInterface
{
  public function pay(float $amount, array $data): array;

  public function withdraw(float $amount, array $data): array;

  public function getBalance(): array;
}