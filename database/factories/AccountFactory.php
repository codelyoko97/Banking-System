<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Account;

class AccountFactory extends Factory
{
    protected $model = Account::class;

    public function definition()
    {
        return [
            'customer_id' => 1,
            'type_id' => 1,
            'number' => 'AC' . $this->faker->unique()->numerify('########'),
            'balance' => $this->faker->randomFloat(4, 0, 5000),
            'status_id' => 1,
        ];
    }
}
