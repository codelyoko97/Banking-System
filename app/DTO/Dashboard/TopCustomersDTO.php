<?php

namespace App\DTO\Dashboard;

class TopCustomersDTO
{
    public array $data;

    public function __construct(array $rows)
    {
        $this->data = array_map(function ($row) {
            return [
                'user_id'      => $row['user_id'],
                'name'         => $row['name'],
                'email'        => $row['email'],
                'total_amount' => (float) $row['total_amount'],
                'tx_count'     => (int) $row['tx_count'],
            ];
        }, $rows);
    }
}
