<?php

namespace App\DTOs;

class ReplyTicketDTO
{
    public int $sender_id;
    public string $sender_type;
    public string $message;

    public function __construct(int $sender_id, string $sender_type, string $message)
    {
        $this->sender_id = $sender_id;
        $this->sender_type = $sender_type;
        $this->message = $message;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['sender_id'],
            $data['sender_type'],
            $data['message']
        );
    }

    public function toArray(): array
    {
        return [
            'sender_id' => $this->sender_id,
            'sender_type' => $this->sender_type,
            'message' => $this->message,
        ];
    }
}
