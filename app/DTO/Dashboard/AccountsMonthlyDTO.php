<?php

namespace App\DTO\Dashboard;

class AccountsMonthlyDTO
{
    public array $data;

    public function __construct(array $rows)
    {
        $this->data = array_map(function ($row) {
            return [
                'date'  => $row['date'],
                'count' => (int) $row['count'],
            ];
        }, $rows);
    }
}
