<?php

namespace Database\Seeders;

use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         $transactions = [
            [
                'account_id' => 1,
                'status' => 'succeeded',
                'amount' => 12.50,
                'type' => 'debit',
                'account_related_id' => null,
                'employee_name' => 'Cafe Rome',
                'description' => 'iced coffee',
                'created_at' => Carbon::now()->subDays(2),
            ],
            [
                'account_id' => 1,
                'status' => 'succeeded',
                'amount' => 45.00,
                'type' => 'debit',
                'account_related_id' => null,
                'employee_name' => 'Supermarket',
                'description' => 'groceries',
                'created_at' => Carbon::now()->subDays(5),
            ],
            [
                'account_id' => 1,
                'status' => 'succeeded',
                'amount' => 1200.00,
                'type' => 'credit',
                'account_related_id' => null,
                'employee_name' => 'Employer',
                'description' => 'monthly salary',
                'created_at' => Carbon::now()->subDays(10),
            ],
            [
                'account_id' => 1,
                'status' => 'succeeded',
                'amount' => 15.99,
                'type' => 'debit',
                'account_related_id' => null,
                'employee_name' => 'Netflix',
                'description' => 'subscription',
                'created_at' => Carbon::now()->subDays(20),
            ],
            [
                'account_id' => 1,
                'status' => 'succeeded',
                'amount' => 60.00,
                'type' => 'debit',
                'account_related_id' => null,
                'employee_name' => 'Gas Station',
                'description' => 'fuel',
                'created_at' => Carbon::now()->subDays(25),
            ],
        ];

        foreach ($transactions as $txn) {
            Transaction::create($txn);
        }

    }
}
