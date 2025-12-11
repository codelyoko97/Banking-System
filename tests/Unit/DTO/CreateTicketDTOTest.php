<?php

namespace Tests\Unit\DTO;

use App\DTO\CreateTicketDTO;
use PHPUnit\Framework\TestCase;

class CreateTicketDTOTest extends TestCase
{
    public function test_constructor_sets_properties()
    {
        $dto = new CreateTicketDTO(1, 'Issue Title', 'Issue message');

        $this->assertEquals(1, $dto->customer_id);
        $this->assertEquals('Issue Title', $dto->title);
        $this->assertEquals('Issue message', $dto->message);
    }

    public function test_from_array_creates_instance()
    {
        $data = [
            'customer_id' => 10,
            'title' => 'Login Problem',
            'message' => 'Cannot login with my credentials',
        ];

        $dto = CreateTicketDTO::fromArray($data);

        $this->assertEquals(10, $dto->customer_id);
        $this->assertEquals('Login Problem', $dto->title);
        $this->assertEquals('Cannot login with my credentials', $dto->message);
    }

    public function test_to_array_returns_correct_array()
    {
        $dto = new CreateTicketDTO(5, 'Payment Issue', 'Payment not processed');

        $array = $dto->toArray();

        $this->assertEquals([
            'customer_id' => 5,
            'title' => 'Payment Issue',
            'message' => 'Payment not processed',
        ], $array);
    }
}