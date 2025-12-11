<?php

namespace Tests\Unit\DTO;

use App\DTO\CreateAccountDTO;
use PHPUnit\Framework\TestCase;

class CreateAccountDTOTest extends TestCase
{
    public function test_constructor_sets_properties()
    {
        $dto = new CreateAccountDTO(1, 2, 300.50, null);

        $this->assertEquals(1, $dto->customer_id);
        $this->assertEquals(2, $dto->type_id);
        $this->assertEquals(300.50, $dto->balance);
        $this->assertNull($dto->account_related_id);
    }

    public function test_from_array_creates_instance()
    {
        $data = [
            'customer_id' => 10,
            'type_id' => 20,
            'balance' => 500.00,
            'account_related_id' => 99,
        ];

        $dto = CreateAccountDTO::fromArray($data);

        $this->assertEquals(10, $dto->customer_id);
        $this->assertEquals(20, $dto->type_id);
        $this->assertEquals(500.00, $dto->balance);
        $this->assertEquals(99, $dto->account_related_id);
    }

    public function test_to_array_returns_correct_array()
    {
        $dto = new CreateAccountDTO(1, 2, null, 5);

        $array = $dto->toArray();

        $this->assertEquals([
            'customer_id' => 1,
            'type_id' => 2,
            'balance' => null,
            'account_related_id' => 5,
        ], $array);
    }
}