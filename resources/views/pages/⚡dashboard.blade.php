<?php

use App\Models\Transaction;
use App\Support\FinancePeriodSummary;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Dashboard')] class extends Component {
    public string $monthFilter = '';

    public function mount(): void
    {
        $this->monthFilter = now()->format('Y-m');
    }

    public function updatedMonthFilter(): void
    {
        unset($this->summary, $this->recentTransactions);
    }

    public function clearMonthFilter(): void
    {
        $this->monthFilter = '';
        unset($this->summary, $this->recentTransactions);
    }

    public function useCurrentMonthFilter(): void
    {
        $this->monthFilter = now()->format('Y-m');
        unset($this->summary, $this->recentTransactions);
    }

    /**
     * @return array{expenses: array{paid: float, pending: float, total: float}, incomes: array{paid: float, pending: float, total: float}, net: float}
     */
    public function getSummaryProperty(): array
    {
        return FinancePeriodSummary::forPeriod(Auth::user(), $this->monthFilter);
    }

    public function getMonthLabelProperty(): string
    {
        if ($this->monthFilter === '') {
            return __('All periods');
        }

        return Carbon::createFromFormat('Y-m', $this->monthFilter)
            ->locale(app()->getLocale())
            ->translatedFormat('F Y');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, Transaction>
     */
    #[Computed]
    public function recentTransactions()
    {
        $query = Auth::user()
            ->transactions()
            ->with('category')
            ->orderByDesc('date')
            ->orderByDesc('id');

        if ($this->monthFilter !== '') {
            [$year, $month] = explode('-', $this->monthFilter);
            $query->whereYear('date', (int) $year)->whereMonth('date', (int) $month);
        }

        return $query->limit(8)->get();
    }
}; ?>

<div>
    <div class="flex flex-col gap-8">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <flux:heading size="xl">{{ __('Dashboard') }}</flux:heading>
                <flux:text class="mt-1">{{ __('A quick overview of your finances for this month.') }}</flux:text>
            </div>
            <div class="flex flex-wrap gap-2">
                <flux:button variant="primary" :href="route('finance.expenses.create')" icon="plus" wire:navigate>
                    {{ __('New expense') }}
                </flux:button>
                <flux:button variant="filled" :href="route('finance.incomes.create')" icon="plus" wire:navigate>
                    {{ __('New income') }}
                </flux:button>
                <flux:button variant="ghost" :href="route('finance.categories.index')" icon="folder" wire:navigate>
                    {{ __('Categories') }}
                </flux:button>
            </div>
        </div>

        <div class="flex flex-wrap items-end gap-3">
            <flux:field class="max-w-xs">
                <flux:label>{{ __('Month') }}</flux:label>
                <flux:input type="month" wire:model.live="monthFilter" />
            </flux:field>
            <flux:button type="button" variant="ghost" size="sm" wire:click="clearMonthFilter">
                {{ __('All periods') }}
            </flux:button>
            <flux:button type="button" variant="ghost" size="sm" wire:click="useCurrentMonthFilter">
                {{ __('Current month') }}
            </flux:button>
        </div>

        <div>
            <flux:heading size="lg" @class(['capitalize' => $this->monthFilter !== ''])>{{ $this->monthLabel }}</flux:heading>
            <flux:text class="mt-1 text-zinc-500">{{ __('Period summary and what is still outstanding.') }}</flux:text>
        </div>

        @include('pages.finance.partials.period-summary', ['summary' => $this->summary])

        <div class="grid gap-4 md:grid-cols-3">
            <a
                href="{{ route('finance.expenses.index') }}"
                wire:navigate
                class="flex flex-col rounded-xl border border-zinc-200 bg-white p-4 transition hover:border-zinc-300 dark:border-zinc-700 dark:bg-zinc-800 dark:hover:border-zinc-600"
            >
                <flux:heading size="sm" class="text-zinc-500">{{ __('Expenses to pay') }}</flux:heading>
                <p class="mt-2 text-2xl font-semibold tabular-nums text-amber-600 dark:text-amber-400">
                    {{ 'R$ '.number_format($this->summary['expenses']['pending'], 2, ',', '.') }}
                </p>
                <flux:text class="mt-2 text-sm">{{ __('Open expenses') }} →</flux:text>
            </a>
            <a
                href="{{ route('finance.incomes.index') }}"
                wire:navigate
                class="flex flex-col rounded-xl border border-zinc-200 bg-white p-4 transition hover:border-zinc-300 dark:border-zinc-700 dark:bg-zinc-800 dark:hover:border-zinc-600"
            >
                <flux:heading size="sm" class="text-zinc-500">{{ __('Income to receive') }}</flux:heading>
                <p class="mt-2 text-2xl font-semibold tabular-nums text-amber-600 dark:text-amber-400">
                    {{ 'R$ '.number_format($this->summary['incomes']['pending'], 2, ',', '.') }}
                </p>
                <flux:text class="mt-2 text-sm">{{ __('Open income') }} →</flux:text>
            </a>
            <a
                href="{{ route('investments.goals.index') }}"
                wire:navigate
                class="flex flex-col rounded-xl border border-zinc-200 bg-white p-4 transition hover:border-zinc-300 dark:border-zinc-700 dark:bg-zinc-800 dark:hover:border-zinc-600"
            >
                <flux:heading size="sm" class="text-zinc-500">{{ __('Total invested') }}</flux:heading>
                <p class="mt-2 text-2xl font-semibold tabular-nums text-emerald-600 dark:text-emerald-400">
                    {{ 'R$ '.number_format($this->summary['expenses']['investmentsPaid'] ?? 0, 2, ',', '.') }}
                </p>
                <flux:text class="mt-2 text-sm">{{ __('Open investments') }} →</flux:text>
            </a>
        </div>

        <livewire:pages::support.ticket-center />

        <div>
            <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
                <flux:heading size="lg">{{ __('Recent transactions') }}</flux:heading>
            </div>

            <div class="overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700">
                <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                    <thead class="bg-zinc-50 dark:bg-zinc-900/50">
                        <tr>
                            <th class="px-4 py-3 text-start text-xs font-medium uppercase text-zinc-500">{{ __('Date') }}</th>
                            <th class="px-4 py-3 text-start text-xs font-medium uppercase text-zinc-500">{{ __('Type') }}</th>
                            <th class="px-4 py-3 text-start text-xs font-medium uppercase text-zinc-500">{{ __('Description') }}</th>
                            <th class="px-4 py-3 text-start text-xs font-medium uppercase text-zinc-500">{{ __('Category') }}</th>
                            <th class="px-4 py-3 text-end text-xs font-medium uppercase text-zinc-500">{{ __('Amount') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 bg-white dark:divide-zinc-700 dark:bg-zinc-800">
                        @forelse ($this->recentTransactions as $transaction)
                            <tr wire:key="dash-tx-{{ $transaction->id }}">
                                <td class="whitespace-nowrap px-4 py-3 text-sm text-zinc-700 dark:text-zinc-200">
                                    {{ $transaction->date->format('d/m/Y') }}
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <flux:badge size="sm" inset="top bottom" :color="$transaction->type === \App\Enums\TransactionType::Expense ? 'zinc' : 'green'">
                                        {{ $transaction->type === \App\Enums\TransactionType::Expense ? __('Expense') : __('Income') }}
                                    </flux:badge>
                                </td>
                                <td class="px-4 py-3 text-sm text-zinc-900 dark:text-zinc-100">
                                    {{ $transaction->description }}
                                </td>
                                <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-300">
                                    {{ $transaction->category?->name ?? '—' }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-end text-sm font-medium tabular-nums text-zinc-900 dark:text-zinc-100">
                                    {{ number_format((float) $transaction->amount, 2, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-sm text-zinc-500">
                                    {{ __('No transactions yet. Add an expense or income to get started.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
