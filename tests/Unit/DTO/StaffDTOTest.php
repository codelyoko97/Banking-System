<?php

namespace Tests\Unit\DTO;

use App\DTO\Dashboard\StaffDTO;
use PHPUnit\Framework\TestCase;

class StaffDTOTest extends TestCase
{
    public function test_constructor_sets_properties()
    {
        $dto = new StaffDTO(
            name: 'Alice',
            email: 'alice@example.com',
            password: 'secret',
            phone: '123456789',
            role_id: 2
        );

        $this->assertEquals('Alice', $dto->name);
        $this->assertEquals('alice@example.com', $dto->email);
        $this->assertEquals('secret', $dto->password);
        $this->assertEquals('123456789', $dto->phone);
        $this->assertEquals(2, $dto->role_id);
    }

    public function test_from_request_creates_instance()
    {
        $request = new class {
            public $name = 'Bob';
            public $email = 'bob@example.com';
            public $password = 'pass123';
            public $phone = '987654321';
            public $role_id = 3;
        };

        $dto = StaffDTO::fromRequest($request);

        $this->assertEquals('Bob', $dto->name);
        $this->assertEquals('bob@example.com', $dto->email);
        $this->assertEquals('pass123', $dto->password);
        $this->assertEquals('987654321', $dto->phone);
        $this->assertEquals(3, $dto->role_id);
    }
}