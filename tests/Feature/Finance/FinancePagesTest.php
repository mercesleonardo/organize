<?php

use App\Enums\{TransactionStatus, TransactionType};
use App\Models\{Category, Transaction, User};
use Livewire\Livewire;

test('visitantes são redirecionados ao login nas rotas de finanças', function () {
    $this->get(route('finance.categories.index'))->assertRedirect(route('login'));
    $this->get(route('finance.expenses.index'))->assertRedirect(route('login'));
    $this->get(route('finance.expenses.create'))->assertRedirect(route('login'));
    $this->get(route('finance.incomes.index'))->assertRedirect(route('login'));
    $this->get(route('finance.incomes.create'))->assertRedirect(route('login'));
});

test('usuários autenticados acessam as páginas de finanças', function () {
    $this->actingAs(User::factory()->create());

    $this->get(route('finance.categories.index'))->assertOk();
    $this->get(route('finance.expenses.index'))->assertOk();
    $this->get(route('finance.expenses.create'))->assertOk();
    $this->get(route('finance.incomes.index'))->assertOk();
    $this->get(route('finance.incomes.create'))->assertOk();
});

test('usuário pode criar uma categoria pelo Livewire', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::test('pages::finance.categories')
        ->call('openCreate')
        ->set('name', 'Moradia')
        ->set('type', 'expense')
        ->call('save')
        ->assertHasNoErrors();

    $category = Category::query()
        ->where('user_id', $user->id)
        ->where('name', 'Moradia')
        ->first();

    expect($category)->not->toBeNull()
        ->and($category->name)->toBe('Moradia');
});

test('usuário pode registrar uma despesa pelo Livewire', function () {
    $user = User::factory()->create();
    seedPaidIncome($user, '200.00');
    $category = Category::factory()->create([
        'user_id' => $user->id,
        'type'    => TransactionType::Expense,
    ]);

    $this->actingAs($user);

    Livewire::test('pages::finance.expense-create')
        ->set('category_id', $category->id)
        ->set('description', 'Compra teste')
        ->set('amount', '100.50')
        ->set('date', '2026-04-05')
        ->set('status', 'paid')
        ->set('is_installment', false)
        ->call('save')
        ->assertHasNoErrors();

    expect(
        Transaction::query()
            ->where('user_id', $user->id)
            ->where('type', TransactionType::Expense)
            ->where('status', TransactionStatus::Paid)
            ->count()
    )->toBe(1);
});

test('não registra despesa paga pelo Livewire sem saldo suficiente', function () {
    $user     = User::factory()->create();
    $category = Category::factory()->create([
        'user_id' => $user->id,
        'type'    => TransactionType::Expense,
    ]);

    $this->actingAs($user);

    Livewire::test('pages::finance.expense-create')
        ->set('category_id', $category->id)
        ->set('description', 'Sem saldo')
        ->set('amount', '10.00')
        ->set('date', '2026-04-05')
        ->set('status', 'paid')
        ->set('is_installment', false)
        ->call('save')
        ->assertHasErrors('status');

    expect(
        Transaction::query()
            ->where('user_id', $user->id)
            ->where('type', TransactionType::Expense)
            ->count()
    )->toBe(0);
});

test('usuário pode registrar despesa parcelada', function () {
    $user     = User::factory()->create();
    $category = Category::factory()->create([
        'user_id' => $user->id,
        'type'    => TransactionType::Expense,
    ]);

    $this->actingAs($user);

    Livewire::test('pages::finance.expense-create')
        ->set('category_id', $category->id)
        ->set('description', 'Notebook')
        ->set('amount', '900.00')
        ->set('date', '2026-04-05')
        ->set('status', 'pending')
        ->set('is_installment', true)
        ->set('installment_count', 3)
        ->call('save')
        ->assertHasNoErrors();

    expect(Transaction::where('user_id', $user->id)->count())->toBe(3);
});
