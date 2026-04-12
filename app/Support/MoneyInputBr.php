<?php

namespace App\Support;

/**
 * Normaliza valores monetários introduzidos no formato brasileiro (milhar `.` e decimal `,`).
 */
final class MoneyInputBr
{
    /**
     * Converte o texto do utilizador para decimal com ponto (ex.: "1.234,56" → "1234.56").
     * Parte fracionária limitada a 2 dígitos (centavos).
     */
    public static function toDecimalString(?string $input): string
    {
        if ($input === null) {
            return '';
        }

        $input = trim(str_replace("\u{00A0}", ' ', $input));
        $input = preg_replace('/\s+/', '', $input) ?? '';

        if ($input === '') {
            return '';
        }

        $lastComma = strrpos($input, ',');
        $lastDot   = strrpos($input, '.');

        if ($lastComma !== false && ($lastDot === false || $lastComma > $lastDot)) {
            $intPart = str_replace('.', '', substr($input, 0, $lastComma));
            $decRaw  = substr($input, $lastComma + 1);
            $decPart = preg_replace('/\D/', '', $decRaw) ?? '';
            $decPart = substr($decPart, 0, 2);
            $intPart = preg_replace('/\D/', '', $intPart) ?? '';

            if ($intPart === '' && $decPart === '') {
                return '';
            }

            return $decPart === '' ? $intPart : $intPart . '.' . $decPart;
        }

        if ($lastDot !== false && $lastComma === false) {
            $parts = explode('.', $input);

            if (count($parts) === 2 && strlen($parts[1]) <= 2 && ctype_digit($parts[1])) {
                $intPart = preg_replace('/\D/', '', $parts[0]) ?? '';

                return $intPart === '' ? '' : $intPart . '.' . $parts[1];
            }

            return preg_replace('/\D/', '', str_replace('.', '', $input)) ?? '';
        }

        return preg_replace('/\D/', '', $input) ?? '';
    }

    /**
     * Formata um número (string com ponto) para exibição pt-BR com 2 casas decimais.
     */
    public static function formatDisplay(?string $dotDecimal): string
    {
        if ($dotDecimal === null || $dotDecimal === '') {
            return '';
        }

        if (!is_numeric($dotDecimal)) {
            return '';
        }

        return number_format((float) $dotDecimal, 2, ',', '.');
    }
}
