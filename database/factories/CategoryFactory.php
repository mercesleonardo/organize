<?php

namespace Database\Factories;

use App\Enums\TransactionType;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => null,
            'name'    => fake()->unique()->words(2, true),
            'icon'    => fake()->optional()->randomElement(['home', 'cart', 'wallet', 'gift']),
            'color'   => fake()->optional()->hexColor(),
            'type'    => fake()->randomElement(TransactionType::cases()),
        ];
    }
}
