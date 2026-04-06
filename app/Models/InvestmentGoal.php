<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};

class InvestmentGoal extends Model
{
    /** @use HasFactory<\Database\Factories\InvestmentGoalFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'target_amount',
        'start_date',
        'target_date',
    ];

    protected function casts(): array
    {
        return [
            'target_amount' => 'decimal:2',
            'start_date'    => 'date',
            'target_date'   => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function contributions(): HasMany
    {
        return $this->hasMany(InvestmentContribution::class)->orderByDesc('date')->orderByDesc('id');
    }

    public function contributedAmount(): float
    {
        return (float) $this->contributions()->sum('amount');
    }

    public function remainingAmount(): float
    {
        return max(0.0, (float) $this->target_amount - $this->contributedAmount());
    }

    public function totalMonths(): ?int
    {
        if ($this->target_date === null) {
            return null;
        }

        /** @var CarbonImmutable $start */
        $start = $this->start_date->toImmutable()->startOfMonth();
        /** @var CarbonImmutable $target */
        $target = $this->target_date->toImmutable()->startOfMonth();

        if ($target->lessThan($start)) {
            return null;
        }

        return $start->diffInMonths($target) + 1;
    }

    public function elapsedMonths(?CarbonImmutable $today = null): int
    {
        $today ??= now()->toImmutable();

        /** @var CarbonImmutable $start */
        $start   = $this->start_date->toImmutable()->startOfMonth();
        $current = $today->startOfMonth();

        if ($current->lessThan($start)) {
            return 0;
        }

        $elapsed = $start->diffInMonths($current) + 1;

        if (($total = $this->totalMonths()) !== null) {
            return min($elapsed, $total);
        }

        return $elapsed;
    }

    public function remainingMonths(?CarbonImmutable $today = null): ?int
    {
        if ($this->target_date === null) {
            return null;
        }

        $today ??= now()->toImmutable();

        /** @var CarbonImmutable $target */
        $target  = $this->target_date->toImmutable()->startOfMonth();
        $current = $today->startOfMonth();

        if ($target->lessThan($current)) {
            return 0;
        }

        return $current->diffInMonths($target) + 1;
    }

    public function suggestedMonthlyAmount(?CarbonImmutable $today = null): ?float
    {
        $remainingMonths = $this->remainingMonths($today);

        if ($remainingMonths === null || $remainingMonths === 0) {
            return null;
        }

        return round($this->remainingAmount() / $remainingMonths, 2);
    }

    public function expectedAmountByNow(?CarbonImmutable $today = null): ?float
    {
        $totalMonths = $this->totalMonths();

        if ($totalMonths === null || $totalMonths === 0) {
            return null;
        }

        $elapsedMonths = $this->elapsedMonths($today);

        return round(((float) $this->target_amount / $totalMonths) * $elapsedMonths, 2);
    }
}
