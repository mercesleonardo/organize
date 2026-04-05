<?php

namespace App\Enums;

enum Frequency: string
{
    case OneTime   = 'one_time';
    case Weekly    = 'weekly';
    case Biweekly  = 'biweekly';
    case Monthly   = 'monthly';
    case Quarterly = 'quarterly';
    case Yearly    = 'yearly';

    public function label(): string
    {
        return match ($this) {
            self::OneTime   => 'Única',
            self::Weekly    => 'Semanal',
            self::Biweekly  => 'Quinzenal',
            self::Monthly   => 'Mensal',
            self::Quarterly => 'Trimestral',
            self::Yearly    => 'Anual',
        };
    }
}
