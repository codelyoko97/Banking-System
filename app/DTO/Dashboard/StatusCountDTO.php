<?php

namespace App\DTO\Dashboard;

class StatusCountDTO
{
    public array $data;

    public function __construct(array $rows)
    {
        $this->data = array_map(function ($row) {
            return [
                'status' => $row['status'],
                'count'  => (int) $row['count'],
            ];
        }, $rows);
    }
}
