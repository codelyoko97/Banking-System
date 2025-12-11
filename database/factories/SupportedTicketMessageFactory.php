<?php

namespace Database\Factories;

use App\Models\SupportedTicketMessage;
use Illuminate\Database\Eloquent\Factories\Factory;

class SupportedTicketMessageFactory extends Factory
{
    protected $model = SupportedTicketMessage::class;

    public function definition(): array
    {
        return [
            'supported_ticket_id' => 1, // اربطه بتذكرة موجودة
            'sender_id' => 1,
            'sender_type' => 'user',
            'message' => $this->faker->sentence(6),
            'is_private' => false,
        ];
    }
}