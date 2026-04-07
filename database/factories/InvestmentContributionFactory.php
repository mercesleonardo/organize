<?php

namespace Database\Factories;

use App\Models\{InvestmentContribution, InvestmentGoal, User};
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InvestmentContribution>
 */
class InvestmentContributionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'investment_goal_id' => InvestmentGoal::factory(),
            'user_id'            => function (array $attributes) {
                $goalId = $attributes['investment_goal_id'] ?? null;

                if ($goalId === null) {
                    return User::factory();
                }

                $goal = InvestmentGoal::query()->find($goalId);

                return $goal?->user_id ?? User::factory();
            },
            'debit_transaction_id' => null,
            'amount'               => fake()->randomFloat(2, 10, 5000),
            'date'                 => fake()->date(),
            'note'                 => fake()->optional()->sentence(),
        ];
    }
}
