<?php

use App\Enums\{TransactionStatus, TransactionType};
use App\Models\{Category, Transaction, User};
use App\Support\FinanceCategoryBreakdown;
use Livewire\Livewire;

test('FinanceCategoryBreakdown agrega despesas por categoria no mês', function (): void {
    $user = User::factory()->create();
    $catA = Category::factory()->create(['user_id' => null, 'type' => TransactionType::Expense, 'name' => 'Alpha']);
    $catB = Category::factory()->create(['user_id' => null, 'type' => TransactionType::Expense, 'name' => 'Beta']);

    Transaction::factory()->create([
        'user_id'     => $user->id,
        'category_id' => $catA->id,
        'amount'      => '300.00',
        'date'        => '2026-06-10',
        'type'        => TransactionType::Expense,
        'status'      => TransactionStatus::Paid,
    ]);
    Transaction::factory()->create([
        'user_id'     => $user->id,
        'category_id' => $catB->id,
        'amount'      => '100.00',
        'date'        => '2026-06-20',
        'type'        => TransactionType::Expense,
        'status'      => TransactionStatus::Pending,
    ]);

    $out = FinanceCategoryBreakdown::forUser($user, TransactionType::Expense, 'month', '2026-06');

    expect($out['total'])->toBe(400.0)
        ->and($out['segments'])->toHaveCount(2)
        ->and($out['segments'][0]['amount'])->toBe(300.0)
        ->and($out['segments'][0]['percent'])->toBe(75.0)
        ->and($out['segments'][1]['percent'])->toBe(25.0);
});

test('FinanceCategoryBreakdown agrega por ano', function (): void {
    $user = User::factory()->create();
    $cat  = Category::factory()->create(['user_id' => null, 'type' => TransactionType::Income]);

    Transaction::factory()->create([
        'user_id'     => $user->id,
        'category_id' => $cat->id,
        'amount'      => '50.00',
        'date'        => '2026-01-01',
        'type'        => TransactionType::Income,
        'status'      => TransactionStatus::Paid,
    ]);
    Transaction::factory()->create([
        'user_id'     => $user->id,
        'category_id' => $cat->id,
        'amount'      => '50.00',
        'date'        => '2026-12-31',
        'type'        => TransactionType::Income,
        'status'      => TransactionStatus::Paid,
    ]);

    $out = FinanceCategoryBreakdown::forUser($user, TransactionType::Income, 'year', '2026');

    expect($out['total'])->toBe(100.0)
        ->and($out['segments'])->toHaveCount(1)
        ->and($out['segments'][0]['percent'])->toBe(100.0);
});

test('página de insights responde 200 para utilizador autenticado', function (): void {
    $this->actingAs(User::factory()->create());

    $this->get(route('finance.insights'))->assertOk();
});

test('componente Livewire de insights monta sem erros', function (): void {
    $this->actingAs(User::factory()->create());

    Livewire::test('pages::finance.insights')
        ->assertSuccessful()
        ->assertSee(__('Get personalized insights'));
});
