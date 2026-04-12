<?php

use App\Enums\{TransactionStatus, TransactionType};
use App\Models\{Category, Transaction, User};
use Livewire\Livewire;

test('policy nega a utilizador comum criar categoria', function () {
    $user = User::factory()->create();

    expect($user->cannot('create', Category::class))->toBeTrue();
});

test('policy permite a administrador ou suporte criar categoria', function () {
    expect(User::factory()->admin()->create()->can('create', Category::class))->toBeTrue()
        ->and(User::factory()->support()->create()->can('create', Category::class))->toBeTrue();
});

test('policy nega exclusão de transação alheia', function () {
    $owner       = User::factory()->create();
    $other       = User::factory()->create();
    $transaction = Transaction::factory()->create(['user_id' => $owner->id]);

    expect($other->cannot('delete', $transaction))->toBeTrue()
        ->and($owner->can('delete', $transaction))->toBeTrue();
});

test('listagem de despesas só exibe lançamentos do próprio usuário', function () {
    $user     = User::factory()->create();
    $other    = User::factory()->create();
    $catUser  = Category::factory()->create(['user_id' => null, 'type' => TransactionType::Expense]);
    $catOther = Category::factory()->create(['user_id' => null, 'type' => TransactionType::Expense]);

    Transaction::factory()->create([
        'user_id'     => $user->id,
        'category_id' => $catUser->id,
        'description' => 'Meu segredo financeiro XYZ',
        'type'        => TransactionType::Expense,
    ]);
    Transaction::factory()->create([
        'user_id'     => $other->id,
        'category_id' => $catOther->id,
        'description' => 'Dado privado do outro ABC',
        'type'        => TransactionType::Expense,
    ]);

    $this->actingAs($user);

    Livewire::test('pages::finance.expenses-index')
        ->set('monthFilter', '')
        ->assertSee('Meu segredo financeiro XYZ')
        ->assertDontSee('Dado privado do outro ABC');
});

test('página de listagem de despesas responde 200', function () {
    $this->actingAs(User::factory()->create());

    $this->get(route('finance.expenses.index'))->assertOk();
});

test('filtro de status restringe despesas na listagem', function () {
    $user = User::factory()->create();
    $cat  = Category::factory()->create(['user_id' => null, 'type' => TransactionType::Expense]);
    Transaction::factory()->create([
        'user_id'     => $user->id,
        'category_id' => $cat->id,
        'description' => 'Aluguel pendente',
        'type'        => TransactionType::Expense,
        'status'      => TransactionStatus::Pending,
    ]);
    Transaction::factory()->create([
        'user_id'     => $user->id,
        'category_id' => $cat->id,
        'description' => 'Luz paga',
        'type'        => TransactionType::Expense,
        'status'      => TransactionStatus::Paid,
    ]);

    $this->actingAs($user);

    Livewire::test('pages::finance.expenses-index')
        ->set('monthFilter', '')
        ->set('statusFilter', 'pending')
        ->assertSee('Aluguel pendente')
        ->assertDontSee('Luz paga');
});

test('filtro de status restringe receitas na listagem', function () {
    $user = User::factory()->create();
    $cat  = Category::factory()->create(['user_id' => null, 'type' => TransactionType::Income]);
    Transaction::factory()->create([
        'user_id'     => $user->id,
        'category_id' => $cat->id,
        'description' => 'Salário recebido',
        'type'        => TransactionType::Income,
        'status'      => TransactionStatus::Paid,
    ]);
    Transaction::factory()->create([
        'user_id'     => $user->id,
        'category_id' => $cat->id,
        'description' => 'Freela pendente',
        'type'        => TransactionType::Income,
        'status'      => TransactionStatus::Pending,
    ]);

    $this->actingAs($user);

    Livewire::test('pages::finance.incomes-index')
        ->set('monthFilter', '')
        ->set('statusFilter', 'paid')
        ->assertSee('Salário recebido')
        ->assertDontSee('Freela pendente');
});

test('busca por descrição filtra despesas na listagem', function () {
    $user = User::factory()->create();
    $cat  = Category::factory()->create(['user_id' => null, 'type' => TransactionType::Expense]);
    Transaction::factory()->create([
        'user_id'     => $user->id,
        'category_id' => $cat->id,
        'description' => 'Compra única XYZ',
        'type'        => TransactionType::Expense,
    ]);
    Transaction::factory()->create([
        'user_id'     => $user->id,
        'category_id' => $cat->id,
        'description' => 'Outro gasto qualquer',
        'type'        => TransactionType::Expense,
    ]);

    $this->actingAs($user);

    Livewire::test('pages::finance.expenses-index')
        ->set('monthFilter', '')
        ->set('search', 'XYZ')
        ->assertSee('Compra única XYZ')
        ->assertDontSee('Outro gasto qualquer');
});
