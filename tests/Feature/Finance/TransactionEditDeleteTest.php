<?php

use App\Enums\{TransactionStatus, TransactionType};
use App\Models\{Category, Transaction, User};
use Livewire\Livewire;

test('usuário pode editar uma despesa na listagem', function () {
    $user     = User::factory()->create();
    $category = Category::factory()->create([
        'user_id' => $user->id,
        'type'    => TransactionType::Expense,
    ]);
    $transaction = Transaction::factory()->create([
        'user_id'     => $user->id,
        'category_id' => $category->id,
        'description' => 'Antes',
        'amount'      => '50.00',
        'type'        => TransactionType::Expense,
        'status'      => TransactionStatus::Pending,
    ]);

    $this->actingAs($user);

    Livewire::test('pages::finance.expenses-index')
        ->call('openEdit', $transaction->id)
        ->set('edit_description', 'Depois')
        ->set('edit_amount', '75.50')
        ->call('saveEdit')
        ->assertHasNoErrors();

    expect($transaction->fresh()->description)->toBe('Depois')
        ->and((string) $transaction->fresh()->amount)->toBe('75.50');
});

test('não altera despesa para pago na edição sem saldo suficiente', function () {
    $user     = User::factory()->create();
    $category = Category::factory()->create([
        'user_id' => $user->id,
        'type'    => TransactionType::Expense,
    ]);
    $transaction = Transaction::factory()->create([
        'user_id'     => $user->id,
        'category_id' => $category->id,
        'description' => 'Pendente',
        'amount'      => '80.00',
        'type'        => TransactionType::Expense,
        'status'      => TransactionStatus::Pending,
    ]);

    $this->actingAs($user);

    Livewire::test('pages::finance.expenses-index')
        ->call('openEdit', $transaction->id)
        ->set('edit_status', 'paid')
        ->call('saveEdit')
        ->assertHasErrors('edit_status');

    expect($transaction->fresh()->status)->toBe(TransactionStatus::Pending);
});

test('usuário pode excluir uma despesa na listagem', function () {
    $user        = User::factory()->create();
    $transaction = Transaction::factory()->create([
        'user_id' => $user->id,
        'type'    => TransactionType::Expense,
    ]);

    $this->actingAs($user);

    Livewire::test('pages::finance.expenses-index')
        ->call('delete', $transaction->id);

    expect(Transaction::find($transaction->id))->toBeNull();
});

test('excluir transação mestre remove parcelas filhas', function () {
    $user     = User::factory()->create();
    $category = Category::factory()->create([
        'user_id' => $user->id,
        'type'    => TransactionType::Expense,
    ]);

    $master = Transaction::factory()->create([
        'user_id'            => $user->id,
        'category_id'        => $category->id,
        'description'        => 'Grupo',
        'total_installments' => 2,
        'installment_number' => 1,
        'parent_id'          => null,
        'type'               => TransactionType::Expense,
    ]);

    $child = Transaction::factory()->create([
        'user_id'            => $user->id,
        'category_id'        => $category->id,
        'description'        => 'Grupo',
        'total_installments' => 2,
        'installment_number' => 2,
        'parent_id'          => $master->id,
        'type'               => TransactionType::Expense,
    ]);

    $this->actingAs($user);

    Livewire::test('pages::finance.expenses-index')
        ->call('delete', $master->id);

    expect(Transaction::query()->whereKey($master->id)->exists())->toBeFalse()
        ->and(Transaction::query()->whereKey($child->id)->exists())->toBeFalse();
});

test('filtro por mês restringe despesas', function () {
    $user     = User::factory()->create();
    $category = Category::factory()->create(['user_id' => $user->id, 'type' => TransactionType::Expense]);

    Transaction::factory()->create([
        'user_id'     => $user->id,
        'category_id' => $category->id,
        'description' => 'Só em abril',
        'date'        => '2026-04-10',
        'type'        => TransactionType::Expense,
    ]);
    Transaction::factory()->create([
        'user_id'     => $user->id,
        'category_id' => $category->id,
        'description' => 'Só em maio',
        'date'        => '2026-05-10',
        'type'        => TransactionType::Expense,
    ]);

    $this->actingAs($user);

    Livewire::test('pages::finance.expenses-index')
        ->set('monthFilter', '2026-04')
        ->assertSee('Só em abril')
        ->assertDontSee('Só em maio');
});

test('usuário pode marcar receita como recebida', function () {
    $user        = User::factory()->create();
    $category    = Category::factory()->create(['user_id' => $user->id, 'type' => TransactionType::Income]);
    $transaction = Transaction::factory()->create([
        'user_id'     => $user->id,
        'category_id' => $category->id,
        'type'        => TransactionType::Income,
        'status'      => TransactionStatus::Pending,
    ]);

    $this->actingAs($user);

    Livewire::test('pages::finance.incomes-index')
        ->set('monthFilter', '')
        ->call('markAsReceived', $transaction->id)
        ->assertHasNoErrors();

    expect($transaction->fresh()->status)->toBe(TransactionStatus::Paid);
});

test('usuário pode marcar despesa como paga', function () {
    $user = User::factory()->create();
    seedPaidIncome($user, '100.00');
    $category    = Category::factory()->create(['user_id' => $user->id, 'type' => TransactionType::Expense]);
    $transaction = Transaction::factory()->create([
        'user_id'     => $user->id,
        'category_id' => $category->id,
        'amount'      => '40.00',
        'type'        => TransactionType::Expense,
        'status'      => TransactionStatus::Pending,
    ]);

    $this->actingAs($user);

    Livewire::test('pages::finance.expenses-index')
        ->set('monthFilter', '')
        ->call('markAsPaid', $transaction->id)
        ->assertHasNoErrors();

    expect($transaction->fresh()->status)->toBe(TransactionStatus::Paid);
});

test('não marca despesa como paga sem saldo suficiente', function () {
    $user        = User::factory()->create();
    $category    = Category::factory()->create(['user_id' => $user->id, 'type' => TransactionType::Expense]);
    $transaction = Transaction::factory()->create([
        'user_id'     => $user->id,
        'category_id' => $category->id,
        'amount'      => '99.00',
        'type'        => TransactionType::Expense,
        'status'      => TransactionStatus::Pending,
    ]);

    $this->actingAs($user);

    Livewire::test('pages::finance.expenses-index')
        ->set('monthFilter', '')
        ->call('markAsPaid', $transaction->id)
        ->assertHasErrors('markPaid');

    expect($transaction->fresh()->status)->toBe(TransactionStatus::Pending);
});

test('marcar despesa como paga atualiza o resumo na listagem', function () {
    $user = User::factory()->create();
    seedPaidIncome($user, '50.00');
    $category    = Category::factory()->create(['user_id' => $user->id, 'type' => TransactionType::Expense]);
    $transaction = Transaction::factory()->create([
        'user_id'     => $user->id,
        'category_id' => $category->id,
        'amount'      => '50.00',
        'date'        => '2026-04-10',
        'type'        => TransactionType::Expense,
        'status'      => TransactionStatus::Pending,
    ]);

    $this->actingAs($user);

    $component = Livewire::test('pages::finance.expenses-index')
        ->set('monthFilter', '2026-04');

    expect($component->get('summary')['expenses']['pending'])->toBe(50.0)
        ->and($component->get('summary')['expenses']['paid'])->toBe(0.0);

    $component->call('markAsPaid', $transaction->id);

    expect($component->get('summary')['expenses']['pending'])->toBe(0.0)
        ->and($component->get('summary')['expenses']['paid'])->toBe(50.0);
});

test('marcar receita como recebida atualiza o resumo na listagem', function () {
    $user        = User::factory()->create();
    $category    = Category::factory()->create(['user_id' => $user->id, 'type' => TransactionType::Income]);
    $transaction = Transaction::factory()->create([
        'user_id'     => $user->id,
        'category_id' => $category->id,
        'amount'      => '80.00',
        'date'        => '2026-04-12',
        'type'        => TransactionType::Income,
        'status'      => TransactionStatus::Pending,
    ]);

    $this->actingAs($user);

    $component = Livewire::test('pages::finance.incomes-index')
        ->set('monthFilter', '2026-04');

    expect($component->get('summary')['incomes']['pending'])->toBe(80.0)
        ->and($component->get('summary')['incomes']['paid'])->toBe(0.0);

    $component->call('markAsReceived', $transaction->id);

    expect($component->get('summary')['incomes']['pending'])->toBe(0.0)
        ->and($component->get('summary')['incomes']['paid'])->toBe(80.0);
});
