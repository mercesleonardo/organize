<?php

use App\Actions\Finance\CreateDefaultCategoriesForUserAction;
use App\Enums\TransactionType;
use App\Models\User;

test('novo utilizador recebe categorias de despesa e receita ao criar conta', function () {
    $user = User::factory()->create();

    expect($user->categories()->count())->toBe(37)
        ->and($user->categories()->where('type', TransactionType::Expense)->count())->toBe(28)
        ->and($user->categories()->where('type', TransactionType::Income)->count())->toBe(9);
});

test('CreateDefaultCategoriesForUserAction pode ser executada sem o observer', function () {
    $user = User::withoutEvents(fn () => User::factory()->create());

    expect($user->categories()->count())->toBe(0);

    app(CreateDefaultCategoriesForUserAction::class)->execute($user);

    expect($user->categories()->count())->toBe(37);
});
