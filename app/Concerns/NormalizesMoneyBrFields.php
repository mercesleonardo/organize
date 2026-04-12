<?php

namespace App\Concerns;

use App\Support\MoneyInputBr;

trait NormalizesMoneyBrFields
{
    /**
     * Normaliza campos monetários introduzidos no formato brasileiro antes de validar ou gravar.
     */
    protected function normalizeMoneyBrFields(string ...$properties): void
    {
        foreach ($properties as $property) {
            if (!property_exists($this, $property)) {
                continue;
            }

            $this->{$property} = MoneyInputBr::toDecimalString((string) ($this->{$property} ?? ''));
        }
    }
}
