<?php

namespace App\Observers;

use App\Models\Transaction;
use App\Support\FinanceSummaryCache;

final class TransactionObserver
{
    public function created(Transaction $transaction): void
    {
        FinanceSummaryCache::bumpForUserId($transaction->user_id);
    }

    public function updated(Transaction $transaction): void
    {
        if ($transaction->wasChanged('user_id')) {
            FinanceSummaryCache::bumpForUserId((int) $transaction->getOriginal('user_id'));
        }

        FinanceSummaryCache::bumpForUserId($transaction->user_id);
    }

    public function deleted(Transaction $transaction): void
    {
        FinanceSummaryCache::bumpForUserId($transaction->user_id);
    }
}
