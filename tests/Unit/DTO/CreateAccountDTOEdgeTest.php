<?php

namespace Tests\Unit\DTO;

use App\DTO\CreateAccountDTO;
use PHPUnit\Framework\TestCase;

class CreateAccountDTOEdgeTest extends TestCase
{
    public function test_from_array_with_valid_data()
    {
        $data = [
            'customer_id' => 1,
            'type_id' => 2,
            'balance' => 100.50,
            'account_related_id' => 5,
        ];

        $dto = CreateAccountDTO::fromArray($data);

        $this->assertEquals(1, $dto->customer_id);
        $this->assertEquals(2, $dto->type_id);
        $this->assertEquals(100.50, $dto->balance);
        $this->assertEquals(5, $dto->account_related_id);
    }

    public function test_from_array_with_missing_optional_fields()
    {
        $data = [
            'customer_id' => 1,
            'type_id' => 2,
        ];

        $dto = CreateAccountDTO::fromArray($data);

        $this->assertEquals(1, $dto->customer_id);
        $this->assertEquals(2, $dto->type_id);
        $this->assertNull($dto->balance);
        $this->assertNull($dto->account_related_id);
    }

    public function test_from_array_with_invalid_types()
    {
        $this->expectException(\TypeError::class);

        $data = [
            'customer_id' => 'not-an-int',
            'type_id' => 'wrong',
            'balance' => 'abc',
            'account_related_id' => 'xyz',
        ];

        // رح يرمي TypeError لأن الأنواع مش صحيحة
        CreateAccountDTO::fromArray($data);
    }

    public function test_to_array_returns_correct_structure()
    {
        $dto = new CreateAccountDTO(1, 2, null, null);

        $array = $dto->toArray();

        $this->assertArrayHasKey('customer_id', $array);
        $this->assertArrayHasKey('type_id', $array);
        $this->assertArrayHasKey('balance', $array);
        $this->assertArrayHasKey('account_related_id', $array);
    }

    public function test_edge_case_negative_balance()
    {
        $dto = new CreateAccountDTO(1, 2, -500.00, null);

        $this->assertEquals(-500.00, $dto->balance);
    }
}