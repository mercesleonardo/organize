<?php

use App\Enums\{TransactionStatus, TransactionType};
use App\Models\{Category, Transaction, User};

test('categoria global permite o mesmo nome em tipos diferentes', function () {
    $a = Category::factory()->create([
        'user_id' => null,
        'name'    => 'Moradia',
        'type'    => TransactionType::Expense,
    ]);

    $b = Category::factory()->create([
        'user_id' => null,
        'name'    => 'Moradia',
        'type'    => TransactionType::Income,
    ]);

    expect($a->type)->toBe(TransactionType::Expense)
        ->and($b->type)->toBe(TransactionType::Income);
});

test('transação usa categoria de plataforma', function () {
    $transaction = Transaction::factory()->create();

    expect($transaction->category->user_id)->toBeNull()
        ->and($transaction->user_id)->not->toBeNull();
});

test('installment children link to parent transaction', function () {
    $user     = User::factory()->create();
    $category = Category::factory()->create([
        'user_id' => null,
        'type'    => TransactionType::Expense,
    ]);

    $master = Transaction::create([
        'user_id'            => $user->id,
        'category_id'        => $category->id,
        'description'        => 'Compra parcelada',
        'amount'             => '300.00',
        'date'               => now()->toDateString(),
        'type'               => TransactionType::Expense,
        'status'             => TransactionStatus::Pending,
        'installment_number' => 1,
        'total_installments' => 3,
        'parent_id'          => null,
    ]);

    $child = Transaction::create([
        'user_id'            => $user->id,
        'category_id'        => $category->id,
        'description'        => 'Compra parcelada',
        'amount'             => '300.00',
        'date'               => now()->addMonth()->toDateString(),
        'type'               => TransactionType::Expense,
        'status'             => TransactionStatus::Pending,
        'installment_number' => 2,
        'total_installments' => 3,
        'parent_id'          => $master->id,
    ]);

    $master->refresh();

    expect($child->parent_id)->toBe($master->id)
        ->and($master->installments)->toHaveCount(1)
        ->and($master->installments->first()->is($child))->toBeTrue();
});
