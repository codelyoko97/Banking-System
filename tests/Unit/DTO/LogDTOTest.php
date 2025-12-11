<?php

namespace Tests\Unit\DTO;

use App\DTO\Dashboard\LogDTO;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class LogDTOTest extends TestCase
{
    public function test_transform_returns_correct_array()
    {
        $log = new class {
            public $id = 1;
            public $user;
            public $action = 'login';
            public $description = 'User logged in';
            public $created_at;

            public function __construct()
            {
                $this->user = new class {
                    public $name = 'John Doe';
                    public $email = 'john@example.com';
                };
                $this->created_at = Carbon::parse('2025-01-01 12:00:00');
            }
        };

        $result = LogDTO::transform($log);

        $this->assertEquals([
            'id'          => 1,
            'user'        => 'John Doe',
            'email'       => 'john@example.com',
            'action'      => 'login',
            'description' => 'User logged in',
            'date'        => '2025-01-01 12:00:00',
        ], $result);
    }

    public function test_transform_handles_null_user()
    {
        $log = new class {
            public $id = 2;
            public $user = null;
            public $action = 'logout';
            public $description = 'User logged out';
            public $created_at;

            public function __construct()
            {
                $this->created_at = Carbon::parse('2025-01-02 15:30:00');
            }
        };

        $result = LogDTO::transform($log);

        $this->assertEquals([
            'id'          => 2,
            'user'        => null,
            'email'       => null,
            'action'      => 'logout',
            'description' => 'User logged out',
            'date'        => '2025-01-02 15:30:00',
        ], $result);
    }
}