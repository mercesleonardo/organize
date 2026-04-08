<?php

namespace App\Support;

use App\Enums\{TransactionStatus, TransactionType};
use App\Models\{Transaction, User};
use Illuminate\Validation\ValidationException;

final class ExpensePaidBalance
{
    /**
     * Capacidade para registrar despesas como pagas: saldo atual (recebidas − despesas pagas).
     * Se {@see $existing} for uma despesa já paga, o valor dela é somado de volta para permitir simular a troca.
     */
    public static function capacityForPaidExpense(User $user, ?Transaction $existing = null): float
    {
        if ($existing !== null && $existing->type !== TransactionType::Expense) {
            throw new \InvalidArgumentException('Apenas despesas.');
        }

        $capacity = FinancePeriodSummary::netBalance($user, '');

        if ($existing !== null && $existing->status === TransactionStatus::Paid) {
            $capacity += (float) $existing->amount;
        }

        return $capacity;
    }

    /**
     * Garante que o utilizador pode registar ou manter uma despesa como paga com o valor indicado.
     *
     * @param  string  $attribute  Chave de validação (ex.: status, edit_status).
     */
    public static function assertCanSetExpensePaid(
        User $user,
        TransactionStatus $newStatus,
        float $newAmount,
        ?Transaction $existing = null,
        string $attribute = 'status',
    ): void {
        if ($newStatus !== TransactionStatus::Paid) {
            return;
        }

        $newAmount = round($newAmount, 2);
        $capacity  = round(self::capacityForPaidExpense($user, $existing), 2);

        if ($capacity < $newAmount) {
            throw ValidationException::withMessages([
                $attribute => __('Insufficient balance. Record received income or leave the expense as pending.'),
            ]);
        }
    }
}
