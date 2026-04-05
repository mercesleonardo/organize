<?php

use App\Enums\{TransactionStatus, TransactionType};
use App\Models\{Category, Transaction, User};

test('category belongs to user and enforces unique name per type', function () {
    $user = User::factory()->create();

    $a = Category::factory()->create([
        'user_id' => $user->id,
        'name'    => 'Moradia',
        'type'    => TransactionType::Expense,
    ]);

    $b = Category::factory()->create([
        'user_id' => $user->id,
        'name'    => 'Moradia',
        'type'    => TransactionType::Income,
    ]);

    expect($a->type)->toBe(TransactionType::Expense)
        ->and($b->type)->toBe(TransactionType::Income);
});

test('transaction factory creates category for same user', function () {
    $transaction = Transaction::factory()->create();

    expect($transaction->user_id)->toBe($transaction->category->user_id);
});

test('installment children link to parent transaction', function () {
    $user     = User::factory()->create();
    $category = Category::factory()->create([
        'user_id' => $user->id,
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
