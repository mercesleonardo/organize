<?php

use App\Support\MoneyInputBr;

test('converte formato brasileiro com milhares e vírgula decimal', function (): void {
    expect(MoneyInputBr::toDecimalString('1.234,56'))->toBe('1234.56');
});

test('converte apenas vírgula decimal', function (): void {
    expect(MoneyInputBr::toDecimalString('12,5'))->toBe('12.5')
        ->and(MoneyInputBr::toDecimalString('0,50'))->toBe('0.50');
});

test('converte apenas dígitos', function (): void {
    expect(MoneyInputBr::toDecimalString('1500'))->toBe('1500');
});

test('string vazia ou null', function (): void {
    expect(MoneyInputBr::toDecimalString(''))->toBe('')
        ->and(MoneyInputBr::toDecimalString(null))->toBe('')
        ->and(MoneyInputBr::toDecimalString('   '))->toBe('');
});

test('limita centavos a dois dígitos', function (): void {
    expect(MoneyInputBr::toDecimalString('10,999'))->toBe('10.99');
});

test('formatDisplay formata com separadores pt-BR', function (): void {
    expect(MoneyInputBr::formatDisplay('1234.5'))->toBe('1.234,50');
});
