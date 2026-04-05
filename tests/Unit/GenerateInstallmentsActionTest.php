<?php

use App\Actions\GenerateInstallmentsAction;
use Carbon\CarbonImmutable;
use Illuminate\Support\Carbon;

test('divide o valor total em parcelas iguais e distribui centavos restantes', function () {
    $action = new GenerateInstallmentsAction();
    $lines  = $action->execute('100.00', Carbon::parse('2026-01-15'), 3);

    expect($lines)->toHaveCount(3)
        ->and(collect($lines)->sum(fn ($l) => (float) $l->amount))->toBe(100.0);

    expect($lines[0]->amount)->toBe('33.34')
        ->and($lines[1]->amount)->toBe('33.33')
        ->and($lines[2]->amount)->toBe('33.33');
});

test('incrementa a data mês a mês', function () {
    $action = new GenerateInstallmentsAction();
    $lines  = $action->execute('50.00', CarbonImmutable::parse('2026-01-15'), 2);

    expect($lines[0]->date->format('Y-m-d'))->toBe('2026-01-15')
        ->and($lines[1]->date->format('Y-m-d'))->toBe('2026-02-15');
});

test('rejeita número de parcelas inválido', function () {
    $action = new GenerateInstallmentsAction();
    $action->execute('10.00', Carbon::now(), 0);
})->throws(InvalidArgumentException::class);

test('rejeita valor negativo', function () {
    $action = new GenerateInstallmentsAction();
    $action->execute('-1.00', Carbon::now(), 1);
})->throws(InvalidArgumentException::class);
