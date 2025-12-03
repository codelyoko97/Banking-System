<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         DB::table('users')->insertOrIgnore([
            [
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'password' => Hash::make('123123123'),
                'role_id' => 1, // Admin
                'phone' => '0911111111',
                'is_verified' => 1,
            ],
            [
                'name' => 'Manager User',
                'email' => 'manager@example.com',
                'password' => Hash::make('123123123'),
                'role_id' => 2, // Manager
                'phone' => '0922222222',
                'is_verified' => 1,
            ],
            [
                'name' => 'Auditor User',
                'email' => 'auditor@example.com',
                'password' => Hash::make('123123123'),
                'role_id' => 3, // Auditor
                'phone' => '0933333333',
                'is_verified' => 1,
            ],
            [
                'name' => 'Teller User',
                'email' => 'teller@example.com',
                'password' => Hash::make('123123123'),
                'role_id' => 4, // Teller
                'phone' => '0944444444',
                'is_verified' => 1,
            ],
            [
                'name' => 'Support Agent User',
                'email' => 'support@example.com',
                'password' => Hash::make('123123123'),
                'role_id' => 5, // Support Agent
                'phone' => '0955555555',
                'is_verified' => 1,
            ],
            [
                'name' => 'Customer User',
                'email' => 'customer@example.com',
                'password' => Hash::make('123123123'),
                'role_id' => 6, // Customer
                'phone' => '0966666666',
                'is_verified' => 1,
            ],
        ]);

    }
}
