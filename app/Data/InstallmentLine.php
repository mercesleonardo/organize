<?php

namespace App\Data;

use Carbon\CarbonInterface;

final readonly class InstallmentLine
{
    public function __construct(
        public int $installmentNumber,
        public int $totalInstallments,
        public string $amount,
        public CarbonInterface $date,
    ) {
    }
}
