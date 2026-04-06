<?php

namespace Database\Factories;

use App\Models\{InvestmentGoal, User};
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InvestmentGoal>
 */
class InvestmentGoalFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start  = fake()->dateTimeBetween('-12 months', 'now');
        $target = fake()->optional(0.8)->dateTimeBetween($start, '+24 months');

        return [
            'user_id'       => User::factory(),
            'name'          => fake()->words(3, true),
            'target_amount' => fake()->randomFloat(2, 1000, 500000),
            'start_date'    => $start->format('Y-m-d'),
            'target_date'   => $target?->format('Y-m-d'),
        ];
    }
}
