<?php

namespace App\Enums;

enum TransactionStatus: string
{
    case Paid    = 'paid';
    case Pending = 'pending';

    /**
     * Rótulo curto para listagens (considera receita vs despesa em "pago").
     */
    public function label(?TransactionType $forType = null): string
    {
        if ($this === self::Pending) {
            return 'Pendente';
        }

        if ($forType === null) {
            return 'Pago';
        }

        return match ($forType) {
            TransactionType::Expense => 'Pago',
            TransactionType::Income  => 'Recebido',
        };
    }
}
