<?php

namespace App\DTO;

class AccountSummaryDTO
{
    public array $data;

    public function __construct(array $rows)
    {
        $this->data = $rows;
    }
}
