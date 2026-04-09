<?php

namespace App\Enums;

enum TicketStatus: string
{
    case Open       = 'open';
    case InProgress = 'in_progress';
    case Resolved   = 'resolved';

    public function label(): string
    {
        return match ($this) {
            self::Open       => 'Aberto',
            self::InProgress => 'Em andamento',
            self::Resolved   => 'Resolvido',
        };
    }
}
