<?php

namespace App\Support;

final class Locale
{
    /**
     * Devolve o código de locale guardado na sessão se estiver entre os suportados.
     */
    public static function acceptedFromSession(): ?string
    {
        $locale    = session()->get('locale');
        $supported = array_keys(config('locales.supported', []));

        if (!is_string($locale) || $locale === '') {
            return null;
        }

        return in_array($locale, $supported, true) ? $locale : null;
    }
}
