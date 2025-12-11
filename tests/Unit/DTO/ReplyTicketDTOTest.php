<?php

namespace Tests\Unit\DTO;

use App\DTO\ReplyTicketDTO;
use PHPUnit\Framework\TestCase;

class ReplyTicketDTOTest extends TestCase
{
    public function test_constructor_sets_properties()
    {
        $dto = new ReplyTicketDTO(1, 'customer', 'This is a reply');

        $this->assertEquals(1, $dto->sender_id);
        $this->assertEquals('customer', $dto->sender_type);
        $this->assertEquals('This is a reply', $dto->message);
    }

    public function test_from_array_creates_instance()
    {
        $data = [
            'sender_id' => 5,
            'sender_type' => 'staff',
            'message' => 'We are checking your ticket',
        ];

        $dto = ReplyTicketDTO::fromArray($data);

        $this->assertEquals(5, $dto->sender_id);
        $this->assertEquals('staff', $dto->sender_type);
        $this->assertEquals('We are checking your ticket', $dto->message);
    }

    public function test_to_array_returns_correct_array()
    {
        $dto = new ReplyTicketDTO(10, 'admin', 'Ticket closed');

        $array = $dto->toArray();

        $this->assertEquals([
            'sender_id' => 10,
            'sender_type' => 'admin',
            'message' => 'Ticket closed',
        ], $array);
    }
}