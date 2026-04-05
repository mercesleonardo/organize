<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

/**
 * Invalida resumos financeiros por utilizador através de um contador de revisão em cache.
 * Cada mudança em transações incrementa a revisão; as chaves de cache incluem a revisão atual.
 */
final class FinanceSummaryCache
{
    private static function revisionKey(int $userId): string
    {
        return "finance_summary_revision:{$userId}";
    }

    public static function bumpForUserId(int $userId): void
    {
        $key = self::revisionKey($userId);
        Cache::forever($key, (int) Cache::get($key, 0) + 1);
    }

    public static function revisionForUserId(int $userId): int
    {
        return (int) Cache::get(self::revisionKey($userId), 0);
    }
}
