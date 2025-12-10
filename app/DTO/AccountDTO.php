<?php

namespace App\DTO;

class AccountDTO
{
    public function __construct(
        public int $id,
        public string $description,
        public float $balance
    ) {}
}
