<?php

namespace Database\Factories;

use App\Enums\{TransactionStatus, TransactionType};
use App\Models\{Category, Transaction, User};
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id'     => User::factory(),
            'category_id' => function (array $attributes) {
                return Category::factory()->create([
                    'user_id' => $attributes['user_id'],
                    'type'    => $attributes['type'] ?? TransactionType::Expense,
                ])->id;
            },
            'description'        => fake()->sentence(),
            'amount'             => fake()->randomFloat(2, 10, 5000),
            'date'               => fake()->date(),
            'type'               => TransactionType::Expense,
            'status'             => TransactionStatus::Paid,
            'installment_number' => 1,
            'total_installments' => 1,
            'parent_id'          => null,
        ];
    }
}
