<?php

namespace App\DTO;

class CreateAccountDTO
{
    public int $customer_id;
    public int $type_id;
    public ?float $balance;
    public ?int $account_related_id;

    public function __construct(int $customer_id, int $type_id, ?float $balance, ?int $account_related_id)
    {
        $this->customer_id = $customer_id;
        $this->type_id = $type_id;
        $this->balance = $balance;
        $this->account_related_id = $account_related_id;
    }

    public static function fromArray(array $data)
    {
        return new self(
            $data['customer_id'],
            $data['type_id'],
            $data['balance'] ?? null,
            $data['account_related_id'] ?? null
        );
    }

    public function toArray(): array
    {
        return [
            'customer_id' => $this->customer_id,
            'type_id' => $this->type_id,
            'balance' => $this->balance,
            'account_related_id' => $this->account_related_id,
        ];
    }
}
