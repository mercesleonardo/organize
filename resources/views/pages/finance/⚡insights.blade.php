<?php

use App\Enums\TransactionType;
use App\Support\FinanceCategoryBreakdown;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Financial insights')] class extends Component {
    /** @var 'expense'|'income' */
    public string $transactionKind = 'expense';

    /** @var 'month'|'year' */
    public string $granularity = 'month';

    /** Período atual: `Y-m` (mês) ou `Y` (ano). */
    public string $period = '';

    public function mount(): void
    {
        $this->period = now()->format('Y-m');
    }

    public function updatedGranularity(string $value): void
    {
        $this->period = $value === 'month'
            ? now()->format('Y-m')
            : now()->format('Y');
        unset($this->breakdown, $this->periodChips);
    }

    public function updatedTransactionKind(): void
    {
        unset($this->breakdown, $this->periodChips);
    }

    public function setPeriod(string $value): void
    {
        $this->period = $value;
        unset($this->breakdown, $this->periodChips);
    }

    public function shiftPeriod(int $delta): void
    {
        if ($this->granularity === 'month') {
            $this->period = CarbonImmutable::createFromFormat('Y-m', $this->period)
                ->addMonths($delta)
                ->format('Y-m');
        } else {
            $this->period = (string) ((int) $this->period + $delta);
        }
        unset($this->breakdown, $this->periodChips);
    }

    /**
     * @return array{total: float, segments: list<array<string, mixed>>}
     */
    #[Computed]
    public function breakdown(): array
    {
        $type = $this->transactionKind === 'income'
            ? TransactionType::Income
            : TransactionType::Expense;

        return FinanceCategoryBreakdown::forUser(Auth::user(), $type, $this->granularity, $this->period);
    }

    /**
     * @return list<array{value: string, label: string, current: bool}>
     */
    #[Computed]
    public function periodChips(): array
    {
        $locale = app()->getLocale();

        if ($this->granularity === 'month') {
            $center = CarbonImmutable::createFromFormat('Y-m', $this->period)->locale($locale);
            $chips   = [];
            for ($i = -4; $i <= 4; $i++) {
                $d       = $center->addMonths($i);
                $value   = $d->format('Y-m');
                $chips[] = [
                    'value'   => $value,
                    'label'   => $d->translatedFormat('M Y'),
                    'current' => $value === $this->period,
                ];
            }

            return $chips;
        }

        $year  = (int) $this->period;
        $chips = [];
        for ($y = $year - 4; $y <= $year + 2; $y++) {
            $value   = (string) $y;
            $chips[] = [
                'value'   => $value,
                'label'   => $value,
                'current' => $value === $this->period,
            ];
        }

        return $chips;
    }
}; ?>

<div>
    <div class="mx-auto flex max-w-3xl flex-col gap-8">
        <flux:callout color="amber" icon="sparkles">
            <flux:callout.heading>{{ __('Get personalized insights') }}</flux:callout.heading>
            <flux:callout.text>{{ __('See how your spending and income are distributed across categories for any month or year.') }}</flux:callout.text>
        </flux:callout>

        <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex flex-col gap-6 p-5 sm:p-8">
                <flux:radio.group wire:model.live="transactionKind" variant="segmented" :label="__('Data type')">
                    <flux:radio value="expense">{{ __('Expenses') }}</flux:radio>
                    <flux:radio value="income">{{ __('Incomes') }}</flux:radio>
                </flux:radio.group>

                <flux:radio.group wire:model.live="granularity" variant="segmented" :label="__('Period')">
                    <flux:radio value="month">{{ __('Month') }}</flux:radio>
                    <flux:radio value="year">{{ __('Year') }}</flux:radio>
                </flux:radio.group>

                <div class="flex items-center gap-2">
                    <flux:button variant="ghost" size="sm" icon="chevron-left" wire:click="shiftPeriod(-1)" :title="__('Previous period')" />
                    <div class="-mx-1 flex flex-1 gap-1 overflow-x-auto pb-1 pt-1 scrollbar-thin">
                        @foreach ($this->periodChips as $chip)
                            <button
                                type="button"
                                wire:key="chip-{{ $chip['value'] }}"
                                wire:click="setPeriod('{{ $chip['value'] }}')"
                                @class([
                                    'shrink-0 rounded-full px-3 py-1.5 text-sm font-medium transition',
                                    'bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900' => $chip['current'],
                                    'text-zinc-600 hover:bg-zinc-100 dark:text-zinc-400 dark:hover:bg-zinc-800' => ! $chip['current'],
                                ])
                            >
                                {{ $chip['label'] }}
                            </button>
                        @endforeach
                    </div>
                    <flux:button variant="ghost" size="sm" icon="chevron-right" wire:click="shiftPeriod(1)" :title="__('Next period')" />
                </div>

                @if ($this->breakdown['total'] <= 0)
                    <flux:callout icon="information-circle" color="zinc">
                        <flux:callout.text>{{ __('No transactions in this period yet.') }}</flux:callout.text>
                    </flux:callout>
                @else
                    <div class="flex flex-col items-stretch gap-8 lg:flex-row lg:items-center">
                        <x-finance.category-donut :breakdown="$this->breakdown" />

                        <div class="min-w-0 flex-1 space-y-3">
                            @foreach ($this->breakdown['segments'] as $segment)
                                <div wire:key="legend-{{ $segment['category_id'] }}-{{ $segment['label'] }}" class="flex items-center gap-2 text-sm">
                                    <span
                                        class="size-2.5 shrink-0 rounded-full"
                                        style="background-color: {{ $segment['color'] }}"
                                    ></span>
                                    <span class="min-w-0 flex-1 truncate text-zinc-700 dark:text-zinc-200">{{ $segment['label'] }}</span>
                                    <span class="shrink-0 tabular-nums text-zinc-500 dark:text-zinc-400">{{ number_format($segment['percent'], 2, ',', '.') }}%</span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="space-y-4 border-t border-zinc-100 pt-6 dark:border-zinc-800">
                        <flux:heading size="sm">{{ __('By category') }}</flux:heading>
                        @foreach ($this->breakdown['segments'] as $segment)
                            <div wire:key="row-{{ $segment['category_id'] }}-{{ $segment['label'] }}" class="flex flex-col gap-2">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex size-9 shrink-0 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800"
                                    >
                                        <x-category-icon
                                            :name="$segment['icon']"
                                            class="size-5 text-zinc-600 dark:text-zinc-300"
                                        />
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-wrap items-baseline justify-between gap-2">
                                            <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $segment['label'] }}</span>
                                            <span class="text-sm tabular-nums text-zinc-600 dark:text-zinc-300">
                                                {{ number_format($segment['percent'], 2, ',', '.') }}%
                                            </span>
                                        </div>
                                        <div class="mt-2 h-2 overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800">
                                            <div
                                                class="h-full rounded-full transition-all"
                                                style="width: {{ min(100, $segment['percent']) }}%; background-color: {{ $segment['color'] }}"
                                            ></div>
                                        </div>
                                    </div>
                                    <span class="shrink-0 text-sm font-semibold tabular-nums text-zinc-900 dark:text-zinc-100">
                                        {{ number_format($segment['amount'], 2, ',', '.') }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
