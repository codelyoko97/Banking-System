<?php

namespace App\DTO;

class TransactionReportDTO
{
    public array $data;

    public function __construct(array $rows)
    {
        $this->data = array_map(function ($row) {
            return [
                'id'         => $row['id'],
                'account_id' => $row['account_id'],
                'status'     => $row['status'],
                'amount'     => (float) $row['amount'],
                'type'       => $row['type'],
                'employee'   => $row['employee_name'] ?? null,
                'date'       => $row['created_at'],
                'description'=> $row['description'] ?? null,
            ];
        }, $rows);
    }
}
