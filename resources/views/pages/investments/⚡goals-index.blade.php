<?php

use App\Concerns\NormalizesMoneyBrFields;
use App\Http\Requests\StoreInvestmentGoalRequest;
use App\Models\InvestmentGoal;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Investments')] class extends Component {
    use NormalizesMoneyBrFields;
    use WithPagination;

    public bool $showCreateModal = false;

    public string $name = '';

    public string $target_amount = '';

    public string $start_date = '';

    public ?string $target_date = null;

    public function mount(): void
    {
        $this->start_date = now()->format('Y-m-d');
    }

    /**
     * @return LengthAwarePaginator<InvestmentGoal>
     */
    #[Computed]
    public function goals(): LengthAwarePaginator
    {
        return Auth::user()
            ->investmentGoals()
            ->orderByDesc('id')
            ->paginate(12);
    }

    public function openCreate(): void
    {
        $this->authorize('create', InvestmentGoal::class);

        $this->resetValidation();
        $this->name = '';
        $this->target_amount = '';
        $this->start_date = now()->format('Y-m-d');
        $this->target_date = null;
        $this->showCreateModal = true;
    }

    public function save()
    {
        $this->authorize('create', InvestmentGoal::class);

        $this->normalizeMoneyBrFields('target_amount');

        $this->validate((new StoreInvestmentGoalRequest())->rules());

        $goal = InvestmentGoal::query()->create([
            'user_id' => Auth::id(),
            'name' => $this->name,
            'target_amount' => number_format((float) $this->target_amount, 2, '.', ''),
            'start_date' => $this->start_date,
            'target_date' => filled($this->target_date) ? $this->target_date : null,
        ]);

        $this->showCreateModal = false;
        $this->reset('name', 'target_amount', 'target_date');

        return redirect()->route('investments.goals.show', $goal);
    }
}; ?>

<div>
    <div class="mx-auto flex max-w-6xl flex-col gap-6 p-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <flux:heading size="xl">{{ __('Investments') }}</flux:heading>
                <flux:text class="mt-1">{{ __('Track goals and contributions without affecting your balance.') }}</flux:text>
            </div>

            <flux:button variant="primary" icon="plus" wire:click="openCreate">
                {{ __('New goal') }}
            </flux:button>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($this->goals as $goal)
                @php
                    $contributed = $goal->contributedAmount();
                    $remaining = $goal->remainingAmount();
                    $suggested = $goal->suggestedMonthlyAmount();
                    $percent = $goal->target_amount > 0 ? min(100, ($contributed / (float) $goal->target_amount) * 100) : 0;
                @endphp

                <a
                    href="{{ route('investments.goals.show', $goal) }}"
                    class="group block rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm transition hover:border-emerald-200 hover:shadow-md dark:border-zinc-700 dark:bg-zinc-800 dark:hover:border-emerald-900/60"
                    wire:navigate
                >
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <div class="truncate text-base font-semibold text-zinc-900 dark:text-white">
                                {{ $goal->name }}
                            </div>
                            <div class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                                {{ __('Target') }}: {{ number_format((float) $goal->target_amount, 2, ',', '.') }}
                            </div>
                        </div>
                        <flux:badge size="sm" inset="top bottom" color="{{ $remaining <= 0 ? 'green' : 'zinc' }}">
                            {{ $remaining <= 0 ? __('Done') : __('In progress') }}
                        </flux:badge>
                    </div>

                    <div class="mt-4">
                        <div class="flex items-center justify-between text-xs text-zinc-500 dark:text-zinc-400">
                            <span>{{ __('Progress') }}</span>
                            <span class="tabular-nums">{{ number_format($percent, 0) }}%</span>
                        </div>
                        <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-700">
                            <div class="h-full rounded-full bg-emerald-600 dark:bg-emerald-500" style="width: {{ $percent }}%"></div>
                        </div>
                    </div>

                    <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
                        <div class="rounded-xl bg-zinc-50 p-3 dark:bg-zinc-900/40">
                            <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Contributed') }}</div>
                            <div class="mt-1 font-medium tabular-nums text-zinc-900 dark:text-white">
                                {{ number_format($contributed, 2, ',', '.') }}
                            </div>
                        </div>
                        <div class="rounded-xl bg-zinc-50 p-3 dark:bg-zinc-900/40">
                            <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Remaining') }}</div>
                            <div class="mt-1 font-medium tabular-nums text-zinc-900 dark:text-white">
                                {{ number_format($remaining, 2, ',', '.') }}
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 text-sm text-zinc-600 dark:text-zinc-300">
                        @if ($suggested !== null)
                            <span class="font-medium">{{ __('Suggested monthly') }}:</span>
                            <span class="tabular-nums">{{ number_format($suggested, 2, ',', '.') }}</span>
                        @else
                            <span class="text-zinc-500 dark:text-zinc-400">{{ __('No deadline set — monthly suggestion unavailable.') }}</span>
                        @endif
                    </div>
                </a>
            @empty
                <div class="rounded-2xl border border-dashed border-zinc-200 bg-white p-8 text-center dark:border-zinc-700 dark:bg-zinc-800 sm:col-span-2 lg:col-span-3">
                    <flux:heading size="lg">{{ __('Create your first goal') }}</flux:heading>
                    <flux:text class="mt-2">{{ __('Set a target and start adding contributions over time.') }}</flux:text>
                    <flux:button class="mt-5" variant="primary" icon="plus" wire:click="openCreate">
                        {{ __('New goal') }}
                    </flux:button>
                </div>
            @endforelse
        </div>

        @if ($this->goals->hasPages())
            <div class="flex justify-center">
                {{ $this->goals->links() }}
            </div>
        @endif
    </div>

    <flux:modal wire:model="showCreateModal" class="md:w-lg">
        <form wire:submit="save" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('New investment goal') }}</flux:heading>
                <flux:text class="mt-2">{{ __('Goals are separate from your income and expenses.') }}</flux:text>
                <flux:text class="mt-2">{{ __('Contributions are recorded as paid expenses to debit your available balance.') }}</flux:text>
            </div>

            <flux:field>
                <flux:label>{{ __('Goal name') }}</flux:label>
                <flux:input wire:model="name" required />
                <flux:error name="name" />
            </flux:field>

            <div class="grid gap-4 sm:grid-cols-2">
                <x-money-input
                    entangled="target_amount"
                    :label="__('Target amount')"
                    :placeholder="__('0.00')"
                    required
                />

                <flux:field>
                    <flux:label>{{ __('Start date') }}</flux:label>
                    <flux:input type="date" wire:model="start_date" required />
                    <flux:error name="start_date" />
                </flux:field>
            </div>

            <flux:field>
                <flux:label>{{ __('Target date (optional)') }}</flux:label>
                <flux:input type="date" wire:model="target_date" />
                <flux:error name="target_date" />
            </flux:field>

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost" type="button">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" type="submit">{{ __('Create goal') }}</flux:button>
            </div>
        </form>
    </flux:modal>
</div>

