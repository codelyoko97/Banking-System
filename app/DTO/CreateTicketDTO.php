<?php

namespace App\DTOs;

class CreateTicketDTO
{
    public int $customer_id;
    public string $title;
    public string $message;

    public function __construct(int $customer_id, string $title, string $message)
    {
        $this->customer_id = $customer_id;
        $this->title = $title;
        $this->message = $message;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['customer_id'],
            $data['title'],
            $data['message']
        );
    }

    public function toArray(): array
    {
        return [
            'customer_id' => $this->customer_id,
            'title' => $this->title,
            'message' => $this->message,
        ];
    }
}
