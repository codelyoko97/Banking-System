<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AccountFeaturesSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('account_features')->insert([
            [
                'account_id' => 1,
                'feature' => 'overdraft',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'account_id' => 1,
                'feature' => 'insurance',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}
