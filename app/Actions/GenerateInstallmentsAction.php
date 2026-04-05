<?php

namespace App\Actions;

use App\Data\InstallmentLine;
use Carbon\{CarbonImmutable, CarbonInterface};
use InvalidArgumentException;

final class GenerateInstallmentsAction
{
    /**
     * Calcula valor e data de cada parcela a partir do total e da data da primeira.
     *
     * @return list<InstallmentLine>
     */
    public function execute(string $totalAmount, CarbonInterface $startDate, int $totalInstallments): array
    {
        if ($totalInstallments < 1) {
            throw new InvalidArgumentException('O número de parcelas deve ser pelo menos 1.');
        }

        $totalCents = (int) round((float) $totalAmount * 100);

        if ($totalCents < 0) {
            throw new InvalidArgumentException('O valor total não pode ser negativo.');
        }

        $amounts  = $this->splitAmountIntoParts($totalCents, $totalInstallments);
        $baseDate = CarbonImmutable::parse($startDate)->startOfDay();
        $lines    = [];

        for ($i = 0; $i < $totalInstallments; $i++) {
            $lines[] = new InstallmentLine(
                installmentNumber: $i + 1,
                totalInstallments: $totalInstallments,
                amount: $amounts[$i],
                date: $baseDate->addMonths($i),
            );
        }

        return $lines;
    }

    /**
     * Divide o total em centavos em partes iguais, distribuindo o resto nas primeiras parcelas.
     *
     * @return list<string>
     */
    private function splitAmountIntoParts(int $totalCents, int $parts): array
    {
        $base      = intdiv($totalCents, $parts);
        $remainder = $totalCents % $parts;
        $amounts   = [];

        for ($i = 0; $i < $parts; $i++) {
            $cents     = $base + ($i < $remainder ? 1 : 0);
            $amounts[] = number_format($cents / 100, 2, '.', '');
        }

        return $amounts;
    }
}
