<?php

namespace App\Support;

use App\Enums\{TransactionStatus, TransactionType};
use App\Models\{Transaction, User};
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

final class FinancePeriodSummary
{
    /**
     * Resumo do período (receitas, despesas, saldo) com cache invalidado pelo {@see TransactionObserver}.
     *
     * @return array{expenses: array{paid: float, pending: float, total: float, investmentsPaid: float}, incomes: array{paid: float, pending: float, total: float}, net: float}
     */
    public static function forPeriod(User $user, string $monthFilter): array
    {
        $revision      = FinanceSummaryCache::revisionForUserId((int) $user->id);
        $periodSegment = $monthFilter === '' ? '__all__' : $monthFilter;
        $cacheKey      = "finance.period_summary.v1.{$user->id}.{$revision}.{$periodSegment}";

        return Cache::remember($cacheKey, now()->addDay(), function () use ($user, $monthFilter) {
            $expenses        = self::sumByTypeAndStatus($user, TransactionType::Expense, $monthFilter);
            $incomes         = self::sumByTypeAndStatus($user, TransactionType::Income, $monthFilter);
            $investmentsPaid = self::sumInvestmentDebitsPaid($user, $monthFilter);

            return [
                'expenses' => [
                    ...$expenses,
                    'investmentsPaid' => $investmentsPaid,
                ],
                'incomes' => $incomes,
                'net'     => $incomes['paid'] - $expenses['paid'],
            ];
        });
    }

    /**
     * @return array{paid: float, pending: float, total: float}
     */
    public static function sumByTypeAndStatus(User $user, TransactionType $type, string $monthFilter): array
    {
        $base    = self::baseQuery($user, $type, $monthFilter);
        $paid    = (float) (clone $base)->where('status', TransactionStatus::Paid)->sum('amount');
        $pending = (float) (clone $base)->where('status', TransactionStatus::Pending)->sum('amount');

        return [
            'paid'    => $paid,
            'pending' => $pending,
            'total'   => $paid + $pending,
        ];
    }

    /**
     * Receitas recebidas (pagas) menos despesas pagas no período (sem cache; prefira {@see forPeriod} na UI).
     */
    public static function netBalance(User $user, string $monthFilter): float
    {
        $incomes  = self::sumByTypeAndStatus($user, TransactionType::Income, $monthFilter);
        $expenses = self::sumByTypeAndStatus($user, TransactionType::Expense, $monthFilter);

        return $incomes['paid'] - $expenses['paid'];
    }

    /**
     * @return Builder<\App\Models\Transaction>
     */
    private static function baseQuery(User $user, TransactionType $type, string $monthFilter): Builder
    {
        $query = Transaction::query()
            ->where('user_id', $user->id)
            ->where('type', $type);

        if ($monthFilter !== '') {
            [$year, $month] = explode('-', $monthFilter);
            $query->whereYear('date', (int) $year)->whereMonth('date', (int) $month);
        }

        return $query;
    }

    /**
     * Soma despesas pagas que representam débitos de aportes em investimentos.
     */
    private static function sumInvestmentDebitsPaid(User $user, string $monthFilter): float
    {
        $query = Transaction::query()
            ->where('transactions.user_id', $user->id)
            ->where('transactions.type', TransactionType::Expense)
            ->where('transactions.status', TransactionStatus::Paid)
            ->leftJoin('categories as c', 'transactions.category_id', '=', 'c.id')
            ->whereIn('c.name', ['Investments', 'Investimentos'])
            ->where(function (Builder $q): void {
                $q
                    ->where('transactions.description', 'like', 'Investment contribution:%')
                    ->orWhere('transactions.description', 'like', 'Aporte de investimento:%');
            });

        if ($monthFilter !== '') {
            [$year, $month] = explode('-', $monthFilter);
            $query->whereYear('transactions.date', (int) $year)->whereMonth('transactions.date', (int) $month);
        }

        return (float) $query->sum('transactions.amount');
    }
}
