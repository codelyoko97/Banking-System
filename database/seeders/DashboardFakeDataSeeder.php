<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Account;
use App\Models\Transaction;
use App\Models\Log; // 🔥 لازم تستورد الموديل Log
use Illuminate\Support\Facades\DB;

class DashboardFakeDataSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        User::truncate();
        Account::truncate();
        Transaction::truncate();
        Log::truncate(); // 🔥 نفرغ جدول اللوجات كمان
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // -------------------------------
        // 1) Fake Employees + Customers
        // -------------------------------
        $roles = [
            'Admin' => 1,
            'Manager' => 2,
            'Auditor' => 3,
            'Teller' => 4,
            'Support Agent' => 5,
            'Customer' => 6,
        ];

        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => bcrypt('123123123'),
            'role_id' => $roles['Admin'],
            'phone' => '0999000000',
            'is_verified' => true,
        ]);

        // 5 customers
        $customers = User::factory()->count(5)->create([
            'role_id' => $roles['Customer'],
            'is_verified' => true,
        ]);

        // 3 employees
        $employees = User::factory()->count(3)->create([
            'role_id' => $roles['Teller'],
            'is_verified' => true,
        ]);

        // -------------------------------
        // 2) Create Accounts for Customers
        // -------------------------------
        $accounts = [];

        foreach ($customers as $customer) {
            $account = Account::create([
                'customer_id' => $customer->id,
                'type_id' => 1,
                'status_id' => 1,
                'number' => "ac00000$customer->id",
                'balance' => rand(500, 2000),
            ]);

            $accounts[] = $account;

            // 🔥 Log إنشاء الحساب
            Log::create([
                'user_id' => $customer->id,
                'action' => 'Account Created',
                'description' => "Account {$account->number} created for {$customer->name} with balance {$account->balance}",
            ]);
        }

        // -------------------------------
        // 3) Create Fake Transactions
        // -------------------------------
        $statuses = ['succeeded', 'failed'];
        $types = ['deposit', 'withdraw', 'transfer'];
        $merchants = ['Amazon', 'Apple', 'Starbucks', 'Nike', 'Adidas', null];

        foreach ($accounts as $account) {
            $count = rand(10, 20);

            for ($i = 0; $i < $count; $i++) {
                $daysAgo = rand(0, 29);
                $status = $statuses[array_rand($statuses)];
                $type   = $types[array_rand($types)];
                $merchant = $merchants[array_rand($merchants)];

                $transaction = Transaction::create([
                    'account_id' => $account->id,
                    'status' => $status,
                    'amount' => rand(10, 500),
                    'type' => $type,
                    'account_related_id' => null,
                    'employee_name' => $employees->random()->name ?? null,
                    'description' => "Test transaction",
                    'created_at' => now()->subDays($daysAgo)->subHours(rand(1, 22)),
                ]);

                // 🔥 Log العملية
                Log::create([
                    'user_id' => $account->customer_id,
                    'action' => 'Transaction Created',
                    'description' => "Transaction {$transaction->id} ({$type}, {$status}) for account {$account->number} amount {$transaction->amount}",
                ]);
            }
        }

        $this->command->info("✔ Dashboard fake data generated successfully with logs.");
    }
}