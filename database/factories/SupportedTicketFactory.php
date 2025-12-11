<?php

namespace Database\Factories;

use App\Models\SupportedTicket;
use Illuminate\Database\Eloquent\Factories\Factory;

class SupportedTicketFactory extends Factory
{
    protected $model = SupportedTicket::class;

    public function definition(): array
    {
        return [
            'customer_id' => 1, // أو اربطه بـ User::factory()->create()->id إذا عندك علاقة
            'title' => $this->faker->sentence(3),
            'message' => $this->faker->paragraph(),
            'status' => 'open',
            'priority' => 'normal',
        ];
    }
}