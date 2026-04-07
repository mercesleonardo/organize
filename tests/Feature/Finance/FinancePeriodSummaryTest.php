<?php

use App\Enums\{TransactionStatus, TransactionType};
use App\Models\{Category, Transaction, User};
use App\Support\{FinancePeriodSummary, FinanceSummaryCache};
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    Cache::flush();
});

test('resume por tipo e status respeita o filtro de mês', function () {
    $user     = User::factory()->create();
    $category = Category::factory()->create([
        'user_id' => $user->id,
        'type'    => TransactionType::Expense,
    ]);

    Transaction::factory()->create([
        'user_id'     => $user->id,
        'category_id' => $category->id,
        'amount'      => '100.00',
        'date'        => '2026-04-10',
        'type'        => TransactionType::Expense,
        'status'      => TransactionStatus::Paid,
    ]);
    Transaction::factory()->create([
        'user_id'     => $user->id,
        'category_id' => $category->id,
        'amount'      => '25.00',
        'date'        => '2026-04-20',
        'type'        => TransactionType::Expense,
        'status'      => TransactionStatus::Pending,
    ]);
    Transaction::factory()->create([
        'user_id'     => $user->id,
        'category_id' => $category->id,
        'amount'      => '999.00',
        'date'        => '2026-05-01',
        'type'        => TransactionType::Expense,
        'status'      => TransactionStatus::Pending,
    ]);

    $april = FinancePeriodSummary::sumByTypeAndStatus($user, TransactionType::Expense, '2026-04');

    expect($april['paid'])->toBe(100.0)
        ->and($april['pending'])->toBe(25.0)
        ->and($april['total'])->toBe(125.0);

    $allMonths = FinancePeriodSummary::sumByTypeAndStatus($user, TransactionType::Expense, '');

    expect($allMonths['total'])->toBe(1124.0);
});

test('saldo usa receitas recebidas e despesas pagas', function () {
    $user       = User::factory()->create();
    $catExpense = Category::factory()->create(['user_id' => $user->id, 'type' => TransactionType::Expense]);
    $catIncome  = Category::factory()->create(['user_id' => $user->id, 'type' => TransactionType::Income]);

    Transaction::factory()->create([
        'user_id'     => $user->id,
        'category_id' => $catIncome->id,
        'amount'      => '500.00',
        'date'        => '2026-06-15',
        'type'        => TransactionType::Income,
        'status'      => TransactionStatus::Paid,
    ]);
    Transaction::factory()->create([
        'user_id'     => $user->id,
        'category_id' => $catExpense->id,
        'amount'      => '200.00',
        'date'        => '2026-06-20',
        'type'        => TransactionType::Expense,
        'status'      => TransactionStatus::Paid,
    ]);

    $net = FinancePeriodSummary::netBalance($user, '2026-06');

    expect($net)->toBe(300.0);
});

test('resumo mostra subtotal de débitos de investimentos nas despesas pagas', function () {
    $user             = User::factory()->create();
    $catIncome        = Category::factory()->create(['user_id' => $user->id, 'type' => TransactionType::Income]);
    $catInvestExpense = Category::factory()->create([
        'user_id' => $user->id,
        'type'    => TransactionType::Expense,
        'name'    => 'Investments',
    ]);

    Transaction::factory()->create([
        'user_id'     => $user->id,
        'category_id' => $catIncome->id,
        'amount'      => '500.00',
        'date'        => '2026-06-15',
        'type'        => TransactionType::Income,
        'status'      => TransactionStatus::Paid,
    ]);

    Transaction::factory()->create([
        'user_id'     => $user->id,
        'category_id' => $catInvestExpense->id,
        'description' => 'Investment contribution: Buy a house',
        'amount'      => '200.00',
        'date'        => '2026-06-20',
        'type'        => TransactionType::Expense,
        'status'      => TransactionStatus::Paid,
    ]);

    $summary = FinancePeriodSummary::forPeriod($user, '2026-06');

    expect($summary['expenses']['paid'])->toBe(200.0)
        ->and($summary['expenses']['investmentsPaid'])->toBe(200.0)
        ->and($summary['net'])->toBe(300.0);
});

test('saldo ignora despesas pendentes', function () {
    $user       = User::factory()->create();
    $catExpense = Category::factory()->create(['user_id' => $user->id, 'type' => TransactionType::Expense]);
    $catIncome  = Category::factory()->create(['user_id' => $user->id, 'type' => TransactionType::Income]);

    Transaction::factory()->create([
        'user_id'     => $user->id,
        'category_id' => $catIncome->id,
        'amount'      => '500.00',
        'date'        => '2026-08-01',
        'type'        => TransactionType::Income,
        'status'      => TransactionStatus::Paid,
    ]);
    Transaction::factory()->create([
        'user_id'     => $user->id,
        'category_id' => $catExpense->id,
        'amount'      => '200.00',
        'date'        => '2026-08-02',
        'type'        => TransactionType::Expense,
        'status'      => TransactionStatus::Pending,
    ]);

    $net = FinancePeriodSummary::netBalance($user, '2026-08');

    expect($net)->toBe(500.0);
});

test('saldo ignora receitas pendentes', function () {
    $user       = User::factory()->create();
    $catExpense = Category::factory()->create(['user_id' => $user->id, 'type' => TransactionType::Expense]);
    $catIncome  = Category::factory()->create(['user_id' => $user->id, 'type' => TransactionType::Income]);

    Transaction::factory()->create([
        'user_id'     => $user->id,
        'category_id' => $catIncome->id,
        'amount'      => '1000.00',
        'date'        => '2026-07-01',
        'type'        => TransactionType::Income,
        'status'      => TransactionStatus::Pending,
    ]);
    Transaction::factory()->create([
        'user_id'     => $user->id,
        'category_id' => $catIncome->id,
        'amount'      => '200.00',
        'date'        => '2026-07-05',
        'type'        => TransactionType::Income,
        'status'      => TransactionStatus::Paid,
    ]);
    Transaction::factory()->create([
        'user_id'     => $user->id,
        'category_id' => $catExpense->id,
        'amount'      => '50.00',
        'date'        => '2026-07-10',
        'type'        => TransactionType::Expense,
        'status'      => TransactionStatus::Paid,
    ]);

    $net = FinancePeriodSummary::netBalance($user, '2026-07');

    expect($net)->toBe(150.0);
});

test('forPeriod invalida cache quando há nova transação', function () {
    $user     = User::factory()->create();
    $category = Category::factory()->create([
        'user_id' => $user->id,
        'type'    => TransactionType::Expense,
    ]);

    $before = FinancePeriodSummary::forPeriod($user, '2026-09');
    expect($before['expenses']['paid'])->toBe(0.0)
        ->and(FinanceSummaryCache::revisionForUserId($user->id))->toBe(0);

    Transaction::factory()->create([
        'user_id'     => $user->id,
        'category_id' => $category->id,
        'amount'      => '42.00',
        'date'        => '2026-09-15',
        'type'        => TransactionType::Expense,
        'status'      => TransactionStatus::Paid,
    ]);

    expect(FinanceSummaryCache::revisionForUserId($user->id))->toBe(1);

    $after = FinancePeriodSummary::forPeriod($user, '2026-09');

    expect($after['expenses']['paid'])->toBe(42.0);
});
