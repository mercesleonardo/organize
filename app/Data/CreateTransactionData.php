<?php

namespace App\Data;

use App\Enums\{TransactionStatus, TransactionType};
use App\Models\User;

final readonly class CreateTransactionData
{
    public function __construct(
        public User $user,
        public int $categoryId,
        public string $description,
        public string $amount,
        public string $date,
        public TransactionType $type,
        public TransactionStatus $status,
        public int $installmentCount = 1,
    ) {
    }
}
