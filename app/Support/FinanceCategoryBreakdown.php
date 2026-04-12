<?php

namespace App\Support;

use App\Enums\TransactionType;
use App\Models\{Category, Transaction, User};
use Illuminate\Support\Collection;

final class FinanceCategoryBreakdown
{
    /**
     * Agrega valores por categoria no período (todas as transações: pagas e pendentes).
     *
     * @param  'month'|'year'  $granularity
     * @return array{
     *     total: float,
     *     segments: list<array{
     *         category_id: int,
     *         name: string,
     *         label: string,
     *         amount: float,
     *         percent: float,
     *         icon: ?string,
     *         color: string
     *     }>
     * }
     */
    public static function forUser(User $user, TransactionType $type, string $granularity, string $period): array
    {
        $query = Transaction::query()
            ->where('user_id', $user->id)
            ->where('type', $type);

        if ($granularity === 'month') {
            [$year, $month] = explode('-', $period);
            $query->whereYear('date', (int) $year)->whereMonth('date', (int) $month);
        } else {
            $query->whereYear('date', (int) $period);
        }

        /** @var Collection<int, object{category_id: int|string|null, total: string|float}> $rows */
        $rows = $query
            ->selectRaw('category_id, SUM(amount) as total')
            ->groupBy('category_id')
            ->orderByDesc('total')
            ->get();

        $categoryIds = $rows->pluck('category_id')->filter()->map(fn ($id) => (int) $id)->unique()->values();
        $categories  = Category::query()->whereIn('id', $categoryIds)->get()->keyBy('id');

        $segments = [];
        $total    = 0.0;

        foreach ($rows as $row) {
            $amount = round((float) $row->total, 2);

            if ($amount <= 0) {
                continue;
            }
            $total += $amount;

            $categoryId = $row->category_id !== null ? (int) $row->category_id : null;
            $category   = $categoryId !== null ? $categories->get($categoryId) : null;

            $name  = $category?->name ?? __('Uncategorized');
            $label = $category !== null ? $category->label() : __('Uncategorized');

            $segments[] = [
                'category_id' => $categoryId ?? 0,
                'name'        => $name,
                'label'       => $label,
                'amount'      => $amount,
                'percent'     => 0.0,
                'icon'        => $category?->icon,
                'color'       => self::resolveSegmentColor($category?->color, $categoryId ?? 0),
            ];
        }

        if ($total > 0) {
            foreach ($segments as $i => $segment) {
                $segments[$i]['percent'] = round(100 * $segment['amount'] / $total, 2);
            }
        }

        return [
            'total'    => round($total, 2),
            'segments' => $segments,
        ];
    }

    /**
     * Cor para gráfico: hex na categoria ou tonalidade estável derivada do id.
     */
    public static function resolveSegmentColor(?string $stored, int $categoryId): string
    {
        if ($stored !== null && $stored !== '' && preg_match('/^#([0-9a-fA-F]{6})$/', $stored, $m)) {
            return '#' . strtolower($m[1]);
        }

        $hue = ($categoryId * 47) % 360;

        return "hsl({$hue}, 62%, 52%)";
    }

    /**
     * Gera o valor CSS {@see conic-gradient()} para um donut (percentagens somam ~100).
     *
     * @param  list<array{color: string, percent: float}>  $segments
     */
    public static function conicGradientStops(array $segments): string
    {
        if ($segments === []) {
            return 'conic-gradient(rgb(212 212 216) 0% 100%)';
        }

        $cursor = 0.0;
        $parts  = [];

        foreach ($segments as $segment) {
            $pct     = max(0.0, min(100.0, (float) $segment['percent']));
            $start   = $cursor;
            $end     = $cursor + $pct;
            $parts[] = sprintf('%s %.4f%% %.4f%%', $segment['color'], $start, $end);
            $cursor  = $end;
        }

        if ($cursor < 100.0 - 0.01) {
            $parts[] = sprintf('rgb(228 228 231) %.4f%% 100%%', $cursor);
        }

        return 'conic-gradient(' . implode(', ', $parts) . ')';
    }
}
