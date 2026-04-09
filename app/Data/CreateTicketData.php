<?php

namespace App\Data;

use App\Models\User;

final readonly class CreateTicketData
{
    public function __construct(
        public User $user,
        public string $subject,
        public string $message,
    ) {
    }
}
