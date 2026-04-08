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
            return __('Pending');
        }

        if ($forType === null) {
            return __('Paid');
        }

        return match ($forType) {
            TransactionType::Expense => __('Paid'),
            TransactionType::Income  => __('Received'),
        };
    }
}
