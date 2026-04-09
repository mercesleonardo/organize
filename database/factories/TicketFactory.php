<?php

namespace Database\Factories;

use App\Enums\TicketStatus;
use App\Models\{Ticket, User};
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ticket>
 */
class TicketFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'subject' => fake()->sentence(6),
            'message' => fake()->paragraph(3),
            'status'  => TicketStatus::Open,
            'reply'   => null,
        ];
    }
}
