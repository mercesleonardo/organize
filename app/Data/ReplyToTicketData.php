<?php

namespace App\Data;

use App\Enums\TicketStatus;
use App\Models\{Ticket, User};

final readonly class ReplyToTicketData
{
    public function __construct(
        public User $agent,
        public Ticket $ticket,
        public string $reply,
        public TicketStatus $status,
    ) {
    }
}
