<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('roles')->insertOrIgnore([
            ['name' => 'Admin'],
            ['name' => 'Manager'],
            ['name' => 'Auditor'],
            ['name' => 'Teller'],
            ['name' => 'Support Agent'],
            ['name' => 'Customer'],
        ]);

    }
}
