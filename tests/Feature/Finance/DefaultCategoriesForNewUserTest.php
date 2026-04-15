<?php

use App\Enums\TransactionType;
use App\Models\{Category, User};

test('categorias de plataforma ficam disponíveis após o seeder', function (): void {
    expect(Category::query()->whereNull('user_id')->count())->toBe(22)
        ->and(Category::query()->whereNull('user_id')->where('type', TransactionType::Expense)->count())->toBe(15)
        ->and(Category::query()->whereNull('user_id')->where('type', TransactionType::Income)->count())->toBe(7);
});

test('novo utilizador não possui categorias próprias na relação', function (): void {
    $user = User::factory()->create();

    expect($user->categories()->count())->toBe(0);
});
