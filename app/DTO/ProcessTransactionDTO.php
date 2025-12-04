<?php

namespace App\DTO;

use App\Models\User;

class ProcessTransactionDTO
{
  public function __construct(
    public int $account_id,
    public float $amount,
    public string $type,
    public ?int $account_related_id,
    public ?string $description,
    public ?string $employee_name,
    public ?User $requestedBy,
  ) {}
}
