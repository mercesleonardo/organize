<?php

use App\Actions\CreateTransactionAction;
use App\Data\CreateTransactionData;
use App\Enums\{TransactionStatus, TransactionType};
use App\Models\{Category, Transaction, User};
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

test('cria uma transação avulsa com uma parcela', function () {
    $user = User::factory()->create();
    seedPaidIncome($user, '100.00');
    $category = Category::factory()->create([
        'user_id' => $user->id,
        'type'    => TransactionType::Expense,
    ]);

    $action     = app(CreateTransactionAction::class);
    $collection = $action->execute(new CreateTransactionData(
        user: $user,
        categoryId: $category->id,
        description: 'Almoço',
        amount: '45.90',
        date: '2026-04-05',
        type: TransactionType::Expense,
        status: TransactionStatus::Paid,
        installmentCount: 1,
    ));

    expect($collection)->toHaveCount(1);

    $transaction = $collection->first();
    expect($transaction)->toBeInstanceOf(Transaction::class)
        ->and($transaction->parent_id)->toBeNull()
        ->and($transaction->installment_number)->toBe(1)
        ->and($transaction->total_installments)->toBe(1)
        ->and((string) $transaction->amount)->toBe('45.90');
});

test('cria transação mestre e parcelas com vínculo e datas mensais', function () {
    $user     = User::factory()->create();
    $category = Category::factory()->create([
        'user_id' => $user->id,
        'type'    => TransactionType::Expense,
    ]);

    $action     = app(CreateTransactionAction::class);
    $collection = $action->execute(new CreateTransactionData(
        user: $user,
        categoryId: $category->id,
        description: 'Notebook',
        amount: '3000.00',
        date: '2026-04-05',
        type: TransactionType::Expense,
        status: TransactionStatus::Pending,
        installmentCount: 3,
    ));

    expect($collection)->toHaveCount(3);

    $master   = $collection->first();
    $children = $collection->slice(1);

    expect($master->parent_id)->toBeNull()
        ->and($master->installment_number)->toBe(1)
        ->and($master->total_installments)->toBe(3);

    foreach ($children as $child) {
        expect($child->parent_id)->toBe($master->id);
    }

    expect($collection->sum(fn (Transaction $t) => (float) $t->amount))->toBe(3000.0);

    expect($master->date->format('Y-m-d'))->toBe('2026-04-05')
        ->and($collection[1]->date->format('Y-m-d'))->toBe('2026-05-05')
        ->and($collection[2]->date->format('Y-m-d'))->toBe('2026-06-05');
});

test('falha quando a categoria não pertence ao usuário', function () {
    $owner    = User::factory()->create();
    $other    = User::factory()->create();
    $category = Category::factory()->create([
        'user_id' => $other->id,
        'type'    => TransactionType::Expense,
    ]);

    $action = app(CreateTransactionAction::class);
    $action->execute(new CreateTransactionData(
        user: $owner,
        categoryId: $category->id,
        description: 'X',
        amount: '10.00',
        date: '2026-04-05',
        type: TransactionType::Expense,
        status: TransactionStatus::Paid,
    ));
})->throws(ModelNotFoundException::class);

test('falha quando o tipo da categoria difere do tipo da transação', function () {
    $user     = User::factory()->create();
    $category = Category::factory()->create([
        'user_id' => $user->id,
        'type'    => TransactionType::Income,
    ]);

    $action = app(CreateTransactionAction::class);
    $action->execute(new CreateTransactionData(
        user: $user,
        categoryId: $category->id,
        description: 'Erro',
        amount: '10.00',
        date: '2026-04-05',
        type: TransactionType::Expense,
        status: TransactionStatus::Paid,
    ));
})->throws(InvalidArgumentException::class);

test('rejeita despesa paga sem saldo suficiente', function () {
    $user     = User::factory()->create();
    $category = Category::factory()->create([
        'user_id' => $user->id,
        'type'    => TransactionType::Expense,
    ]);

    $action = app(CreateTransactionAction::class);

    expect(fn () => $action->execute(new CreateTransactionData(
        user: $user,
        categoryId: $category->id,
        description: 'Almoço',
        amount: '10.00',
        date: '2026-04-05',
        type: TransactionType::Expense,
        status: TransactionStatus::Paid,
        installmentCount: 1,
    )))->toThrow(ValidationException::class);
});

test('permite despesa paga quando há saldo suficiente', function () {
    $user = User::factory()->create();
    seedPaidIncome($user, '50.00');
    $category = Category::factory()->create([
        'user_id' => $user->id,
        'type'    => TransactionType::Expense,
    ]);

    $action     = app(CreateTransactionAction::class);
    $collection = $action->execute(new CreateTransactionData(
        user: $user,
        categoryId: $category->id,
        description: 'Almoço',
        amount: '49.99',
        date: '2026-04-05',
        type: TransactionType::Expense,
        status: TransactionStatus::Paid,
        installmentCount: 1,
    ));

    expect($collection)->toHaveCount(1);
});
