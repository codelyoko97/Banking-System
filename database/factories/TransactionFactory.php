<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Transaction;
use App\Models\Account;

class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition()
    {
        return [
            'account_id' => Account::factory(),
            'type' => 'deposit',
            'status' => 'completed',
            'amount' => 100,
            'description' => null,
            'role_id' => 1,
        ];
    }
}
