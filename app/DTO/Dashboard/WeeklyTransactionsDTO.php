<?php

namespace App\DTO\Dashboard;

class WeeklyTransactionsDTO
{
    public array $data;

    public function __construct(array $rows)
    {
        $this->data = array_map(function ($row) {
            return [
                'day'   => $row['day'],
                'count' => (int) $row['count'],
            ];
        }, $rows);
    }
}
