<?php

use App\Enums\{TransactionStatus, TransactionType};
use App\Models\{InvestmentContribution, InvestmentGoal, Transaction, User};
use Livewire\Livewire;

test('guests are redirected from investments pages', function () {
    $this->get(route('investments.goals.index'))->assertRedirect(route('login'));
});

test('user can create an investment goal and see it', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::test('pages::investments.goals-index')
        ->call('openCreate')
        ->set('name', 'Buy a house')
        ->set('target_amount', '1000.00')
        ->set('start_date', '2026-01-01')
        ->set('target_date', '2026-12-01')
        ->call('save')
        ->assertHasNoErrors();

    $goal = InvestmentGoal::query()->where('user_id', $user->id)->first();

    expect($goal)->not->toBeNull()
        ->and($goal->name)->toBe('Buy a house')
        ->and((string) $goal->target_amount)->toBe('1000.00');

    $this->get(route('investments.goals.show', $goal))->assertOk();
});

test('user can add contributions and stats update', function () {
    $user = User::factory()->create();
    $goal = InvestmentGoal::factory()->create([
        'user_id'       => $user->id,
        'target_amount' => '1200.00',
        'start_date'    => '2026-01-01',
        'target_date'   => '2026-12-01',
    ]);

    $this->actingAs($user);
    seedPaidIncome($user, '500.00');

    $component = Livewire::test('pages::investments.goal-show', ['goal' => $goal])
        ->set('amount', '200.00')
        ->set('date', '2026-02-01')
        ->call('addContribution')
        ->assertHasNoErrors();

    $contribution = InvestmentContribution::query()->where('investment_goal_id', $goal->id)->first();

    expect($contribution)->not->toBeNull()
        ->and($contribution->debit_transaction_id)->not->toBeNull();

    expect(Transaction::query()
        ->whereKey($contribution->debit_transaction_id)
        ->where('user_id', $user->id)
        ->where('type', TransactionType::Expense)
        ->where('status', TransactionStatus::Paid)
        ->exists())->toBeTrue();

    $stats = $component->get('stats');

    expect($stats['contributed'])->toBe(200.0)
        ->and($stats['remaining'])->toBe(1000.0)
        ->and($stats['suggested'])->toBeFloat();
});

test('user can edit and delete a goal', function () {
    $user = User::factory()->create();
    $goal = InvestmentGoal::factory()->create([
        'user_id'       => $user->id,
        'name'          => 'Old',
        'target_amount' => '100.00',
        'start_date'    => '2026-01-01',
    ]);

    $this->actingAs($user);

    Livewire::test('pages::investments.goal-show', ['goal' => $goal])
        ->call('openEditGoal')
        ->set('edit_name', 'New')
        ->set('edit_target_amount', '200.00')
        ->set('edit_start_date', '2026-01-01')
        ->call('saveGoal')
        ->assertHasNoErrors();

    expect($goal->fresh()->name)->toBe('New')
        ->and((string) $goal->fresh()->target_amount)->toBe('200.00');

    Livewire::test('pages::investments.goal-show', ['goal' => $goal])
        ->call('deleteGoal');

    expect(InvestmentGoal::query()->whereKey($goal->id)->exists())->toBeFalse();
});

test('user can edit and delete a contribution', function () {
    $user = User::factory()->create();
    $goal = InvestmentGoal::factory()->create(['user_id' => $user->id]);
    seedPaidIncome($user, '100.00');
    $c = InvestmentContribution::factory()->create([
        'investment_goal_id'   => $goal->id,
        'user_id'              => $user->id,
        'debit_transaction_id' => Transaction::factory()->create([
            'user_id' => $user->id,
            'type'    => TransactionType::Expense,
            'status'  => TransactionStatus::Paid,
            'amount'  => '10.00',
            'date'    => '2026-02-01',
        ])->id,
        'amount' => '10.00',
        'date'   => '2026-02-01',
        'note'   => 'Old',
    ]);

    $this->actingAs($user);

    Livewire::test('pages::investments.goal-show', ['goal' => $goal])
        ->call('openEditContribution', $c->id)
        ->set('edit_amount', '25.00')
        ->set('edit_date', '2026-02-02')
        ->set('edit_note', 'New')
        ->call('saveContribution')
        ->assertHasNoErrors();

    expect((string) $c->fresh()->amount)->toBe('25.00')
        ->and($c->fresh()->date->format('Y-m-d'))->toBe('2026-02-02')
        ->and($c->fresh()->note)->toBe('New');

    expect((string) Transaction::query()->find($c->fresh()->debit_transaction_id)?->amount)->toBe('25.00');

    Livewire::test('pages::investments.goal-show', ['goal' => $goal])
        ->call('deleteContribution', $c->id)
        ->assertHasNoErrors();

    expect(InvestmentContribution::query()->whereKey($c->id)->exists())->toBeFalse();
    expect(Transaction::query()->whereKey($c->debit_transaction_id)->exists())->toBeFalse();
});

test('não registra aporte sem saldo suficiente', function () {
    $user = User::factory()->create();
    $goal = InvestmentGoal::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user);

    Livewire::test('pages::investments.goal-show', ['goal' => $goal])
        ->set('amount', '200.00')
        ->set('date', '2026-02-01')
        ->call('addContribution')
        ->assertHasErrors('amount');
});

test('user cannot view another users goal', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();

    $goal = InvestmentGoal::factory()->create([
        'user_id' => $owner->id,
    ]);

    $this->actingAs($other);

    $this->get(route('investments.goals.show', $goal))->assertForbidden();
});
