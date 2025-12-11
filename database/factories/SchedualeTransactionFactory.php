<?php

namespace Database\Factories;

use App\Models\SchedualeTransaction;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class SchedualeTransactionFactory extends Factory
{
    protected $model = SchedualeTransaction::class;

    public function definition()
    {
        return [
            'account_id' => 1, // يمكنك تغييره لاحقًا حسب الحاجة
            'amount' => $this->faker->randomFloat(2, 10, 1000),
            'type' => $this->faker->randomElement(['deposit', 'withdraw', 'transfer']),
            'account_related_id' => null,
            'frequency' => $this->faker->randomElement(['daily', 'weekly', 'monthly']),
            'next_run' => Carbon::now()->addDay(),
            'day_of_month' => null,
            'end_date' => null,
            'active' => true,
        ];
    }
}