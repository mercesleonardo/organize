<?php

use App\Http\Requests\StoreInvestmentContributionRequest;
use App\Http\Requests\UpdateInvestmentGoalRequest;
use App\Models\InvestmentContribution;
use App\Models\InvestmentGoal;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Investment goal')] class extends Component {
    use WithPagination;

    public InvestmentGoal $goal;

    public bool $showEditGoalModal = false;

    public string $edit_name = '';

    public string $edit_target_amount = '';

    public string $edit_start_date = '';

    public ?string $edit_target_date = null;

    public string $amount = '';

    public string $date = '';

    public string $note = '';

    public bool $showEditContributionModal = false;

    public ?int $editingContributionId = null;

    public string $edit_amount = '';

    public string $edit_date = '';

    public string $edit_note = '';

    public function mount(InvestmentGoal $goal): void
    {
        $this->goal = $goal;
        $this->authorize('view', $this->goal);

        $this->date = now()->format('Y-m-d');
    }

    /**
     * @return LengthAwarePaginator<InvestmentContribution>
     */
    #[Computed]
    public function contributions(): LengthAwarePaginator
    {
        return $this->goal
            ->contributions()
            ->paginate(10);
    }

    #[Computed]
    public function stats(): array
    {
        $contributed = $this->goal->contributedAmount();
        $remaining = $this->goal->remainingAmount();
        $suggested = $this->goal->suggestedMonthlyAmount();
        $expected = $this->goal->expectedAmountByNow();
        $remainingMonths = $this->goal->remainingMonths();
        $totalMonths = $this->goal->totalMonths();
        $elapsedMonths = $this->goal->elapsedMonths();

        $percent = $this->goal->target_amount > 0
            ? min(100, ($contributed / (float) $this->goal->target_amount) * 100)
            : 0;

        return [
            'contributed' => $contributed,
            'remaining' => $remaining,
            'suggested' => $suggested,
            'expected' => $expected,
            'remainingMonths' => $remainingMonths,
            'totalMonths' => $totalMonths,
            'elapsedMonths' => $elapsedMonths,
            'percent' => $percent,
        ];
    }

    public function openEditGoal(): void
    {
        $this->authorize('update', $this->goal);

        $this->resetValidation();
        $this->edit_name = $this->goal->name;
        $this->edit_target_amount = (string) $this->goal->target_amount;
        $this->edit_start_date = $this->goal->start_date->format('Y-m-d');
        $this->edit_target_date = $this->goal->target_date?->format('Y-m-d');
        $this->showEditGoalModal = true;
    }

    public function saveGoal(): void
    {
        $this->authorize('update', $this->goal);

        $this->validate((new UpdateInvestmentGoalRequest())->rules());

        $this->goal->update([
            'name' => $this->edit_name,
            'target_amount' => number_format((float) $this->edit_target_amount, 2, '.', ''),
            'start_date' => $this->edit_start_date,
            'target_date' => filled($this->edit_target_date) ? $this->edit_target_date : null,
        ]);

        $this->showEditGoalModal = false;
        unset($this->stats);
    }

    public function deleteGoal(): mixed
    {
        $this->authorize('delete', $this->goal);

        $this->goal->delete();

        return redirect()->route('investments.goals.index');
    }

    public function addContribution(): void
    {
        $this->authorize('create', InvestmentContribution::class);
        $this->authorize('update', $this->goal);

        $this->validate((new StoreInvestmentContributionRequest())->rules());

        InvestmentContribution::query()->create([
            'investment_goal_id' => $this->goal->id,
            'user_id' => Auth::id(),
            'amount' => number_format((float) $this->amount, 2, '.', ''),
            'date' => $this->date,
            'note' => filled($this->note) ? $this->note : null,
        ]);

        $this->reset('amount', 'note');
        $this->resetPage();
        unset($this->stats);
    }

    public function openEditContribution(int $contributionId): void
    {
        $contribution = InvestmentContribution::query()
            ->whereKey($contributionId)
            ->where('investment_goal_id', $this->goal->id)
            ->whereBelongsTo(Auth::user())
            ->firstOrFail();

        $this->authorize('update', $contribution);

        $this->resetValidation();
        $this->editingContributionId = $contribution->id;
        $this->edit_amount = (string) $contribution->amount;
        $this->edit_date = $contribution->date->format('Y-m-d');
        $this->edit_note = (string) ($contribution->note ?? '');
        $this->showEditContributionModal = true;
    }

    public function saveContribution(): void
    {
        $contribution = InvestmentContribution::query()
            ->whereKey($this->editingContributionId)
            ->where('investment_goal_id', $this->goal->id)
            ->whereBelongsTo(Auth::user())
            ->firstOrFail();

        $this->authorize('update', $contribution);

        $rules = (new StoreInvestmentContributionRequest())->rules();
        $rules = array_combine(
            array_map(fn (string $key) => 'edit_'.$key, array_keys($rules)),
            array_values($rules),
        );

        $this->validate($rules);

        $contribution->update([
            'amount' => number_format((float) $this->edit_amount, 2, '.', ''),
            'date' => $this->edit_date,
            'note' => filled($this->edit_note) ? $this->edit_note : null,
        ]);

        $this->showEditContributionModal = false;
        $this->editingContributionId = null;
        unset($this->stats);
    }

    public function deleteContribution(int $contributionId): void
    {
        $contribution = InvestmentContribution::query()
            ->whereKey($contributionId)
            ->where('investment_goal_id', $this->goal->id)
            ->whereBelongsTo(Auth::user())
            ->firstOrFail();

        $this->authorize('delete', $contribution);

        $contribution->delete();

        $this->resetPage();
        unset($this->stats);
    }
}; ?>

<div class="mx-auto max-w-6xl p-6">
    <div class="mb-6 flex flex-wrap items-start justify-between gap-3">
        <div class="min-w-0">
            <flux:button variant="ghost" :href="route('investments.goals.index')" icon="arrow-left" wire:navigate>
                {{ __('Back') }}
            </flux:button>

            <flux:heading size="xl" class="mt-4 truncate">{{ $goal->name }}</flux:heading>
            <flux:text class="mt-1">
                {{ __('Target') }}:
                <span class="font-medium tabular-nums text-zinc-900 dark:text-white">{{ number_format((float) $goal->target_amount, 2, ',', '.') }}</span>
                <span class="mx-2 text-zinc-300 dark:text-zinc-700">•</span>
                {{ __('Start') }}:
                <span class="tabular-nums">{{ $goal->start_date->format('d/m/Y') }}</span>
                @if ($goal->target_date)
                    <span class="mx-2 text-zinc-300 dark:text-zinc-700">•</span>
                    {{ __('Deadline') }}:
                    <span class="tabular-nums">{{ $goal->target_date->format('d/m/Y') }}</span>
                @endif
            </flux:text>
        </div>

        @php($s = $this->stats)
        <div class="flex items-center gap-2">
            @if ($s['expected'] !== null)
                @if ($s['contributed'] >= $s['expected'])
                    <flux:badge size="sm" inset="top bottom" color="green">{{ __('Ahead') }}</flux:badge>
                @else
                    <flux:badge size="sm" inset="top bottom" color="amber">{{ __('Behind') }}</flux:badge>
                @endif
            @endif
            <flux:badge size="sm" inset="top bottom" color="{{ $s['remaining'] <= 0 ? 'green' : 'zinc' }}">
                {{ $s['remaining'] <= 0 ? __('Done') : __('In progress') }}
            </flux:badge>

            <flux:button variant="ghost" size="sm" icon="pencil" wire:click="openEditGoal">
                {{ __('Edit') }}
            </flux:button>
            <flux:button
                variant="danger"
                size="sm"
                icon="trash"
                wire:click="deleteGoal"
                wire:confirm="{{ __('Delete this goal?') }}"
            >
                {{ __('Delete') }}
            </flux:button>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-5">
        <div class="space-y-6 lg:col-span-3">
            <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
                <div class="flex items-start justify-between gap-6">
                    <div class="min-w-0">
                        <flux:heading size="lg">{{ __('Progress') }}</flux:heading>
                        <flux:text class="mt-2 text-sm">
                            {{ __('Contributed') }}:
                            <span class="font-medium tabular-nums text-zinc-900 dark:text-white">{{ number_format($s['contributed'], 2, ',', '.') }}</span>
                            <span class="mx-2 text-zinc-300 dark:text-zinc-700">•</span>
                            {{ __('Remaining') }}:
                            <span class="font-medium tabular-nums text-zinc-900 dark:text-white">{{ number_format($s['remaining'], 2, ',', '.') }}</span>
                        </flux:text>
                    </div>

                    <div class="text-end">
                        <div class="text-sm font-medium tabular-nums text-zinc-900 dark:text-white">{{ number_format($s['percent'], 0) }}%</div>
                        <div class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">{{ __('of target') }}</div>
                    </div>
                </div>

                <div class="mt-5 h-2.5 w-full overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-700">
                    <div class="h-full rounded-full bg-emerald-600 dark:bg-emerald-500" style="width: {{ $s['percent'] }}%"></div>
                </div>

                <div class="mt-6 grid gap-3 sm:grid-cols-3">
                    <div class="rounded-xl bg-zinc-50 p-4 dark:bg-zinc-900/40">
                        <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Elapsed') }}</div>
                        <div class="mt-1 font-medium tabular-nums text-zinc-900 dark:text-white">
                            {{ $s['elapsedMonths'] }}
                            @if ($s['totalMonths'] !== null)
                                / {{ $s['totalMonths'] }}
                            @endif
                            {{ __('months') }}
                        </div>
                    </div>

                    <div class="rounded-xl bg-zinc-50 p-4 dark:bg-zinc-900/40">
                        <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Expected by now') }}</div>
                        <div class="mt-1 font-medium tabular-nums text-zinc-900 dark:text-white">
                            {{ $s['expected'] !== null ? number_format($s['expected'], 2, ',', '.') : '—' }}
                        </div>
                    </div>

                    <div class="rounded-xl bg-zinc-50 p-4 dark:bg-zinc-900/40">
                        <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Suggested monthly') }}</div>
                        <div class="mt-1 font-medium tabular-nums text-zinc-900 dark:text-white">
                            {{ $s['suggested'] !== null ? number_format($s['suggested'], 2, ',', '.') : '—' }}
                        </div>
                    </div>
                </div>

                @if ($goal->target_date === null)
                    <flux:callout class="mt-6" icon="information-circle">
                        <flux:callout.text>{{ __('Add a deadline to unlock the monthly suggestion.') }}</flux:callout.text>
                    </flux:callout>
                @endif
            </div>

            <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
                <flux:heading size="lg">{{ __('Contributions') }}</flux:heading>
                <flux:text class="mt-2">{{ __('Add values over time and track your progress.') }}</flux:text>

                <form wire:submit="addContribution" class="mt-6 grid gap-4 sm:grid-cols-3">
                    <flux:field class="sm:col-span-1">
                        <flux:label>{{ __('Amount') }}</flux:label>
                        <flux:input type="number" step="0.01" min="0" wire:model="amount" required />
                        <flux:error name="amount" />
                    </flux:field>

                    <flux:field class="sm:col-span-1">
                        <flux:label>{{ __('Date') }}</flux:label>
                        <flux:input type="date" wire:model="date" required />
                        <flux:error name="date" />
                    </flux:field>

                    <flux:field class="sm:col-span-1">
                        <flux:label>{{ __('Note (optional)') }}</flux:label>
                        <flux:input wire:model="note" />
                        <flux:error name="note" />
                    </flux:field>

                    <div class="sm:col-span-3 flex justify-end">
                        <flux:button variant="primary" type="submit" icon="plus">
                            {{ __('Add contribution') }}
                        </flux:button>
                    </div>
                </form>

                <div class="mt-6 overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700">
                    <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                        <thead class="bg-zinc-50 dark:bg-zinc-900/50">
                            <tr>
                                <th class="px-4 py-3 text-start text-xs font-medium uppercase text-zinc-500">{{ __('Date') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-medium uppercase text-zinc-500">{{ __('Note') }}</th>
                                <th class="px-4 py-3 text-end text-xs font-medium uppercase text-zinc-500">{{ __('Amount') }}</th>
                                <th class="px-4 py-3 text-end text-xs font-medium uppercase text-zinc-500">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 bg-white dark:divide-zinc-700 dark:bg-zinc-800">
                            @forelse ($this->contributions as $c)
                                <tr wire:key="contrib-{{ $c->id }}">
                                    <td class="whitespace-nowrap px-4 py-3 text-sm text-zinc-700 dark:text-zinc-200">
                                        {{ $c->date->format('d/m/Y') }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-300">
                                        {{ $c->note ?: '—' }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-end text-sm font-medium tabular-nums text-zinc-900 dark:text-zinc-100">
                                        {{ number_format((float) $c->amount, 2, ',', '.') }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-end text-sm">
                                        <flux:button size="sm" variant="ghost" wire:click="openEditContribution({{ $c->id }})">
                                            {{ __('Edit') }}
                                        </flux:button>
                                        <flux:button
                                            size="sm"
                                            variant="danger"
                                            wire:click="deleteContribution({{ $c->id }})"
                                            wire:confirm="{{ __('Delete this contribution?') }}"
                                        >
                                            {{ __('Delete') }}
                                        </flux:button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-8 text-center text-sm text-zinc-500">
                                        {{ __('No contributions yet.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($this->contributions->hasPages())
                    <div class="mt-4 flex justify-center">
                        {{ $this->contributions->links() }}
                    </div>
                @endif
            </div>
        </div>

        <div class="space-y-6 lg:col-span-2">
            <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
                <flux:heading size="lg">{{ __('Monthly guidance') }}</flux:heading>
                <flux:text class="mt-2">{{ __('Based on your deadline and what is still missing.') }}</flux:text>

                <div class="mt-5 space-y-3 text-sm">
                    <div class="flex items-center justify-between gap-4">
                        <span class="text-zinc-600 dark:text-zinc-300">{{ __('Remaining') }}</span>
                        <span class="font-medium tabular-nums text-zinc-900 dark:text-white">{{ number_format($s['remaining'], 2, ',', '.') }}</span>
                    </div>

                    <div class="flex items-center justify-between gap-4">
                        <span class="text-zinc-600 dark:text-zinc-300">{{ __('Months remaining') }}</span>
                        <span class="font-medium tabular-nums text-zinc-900 dark:text-white">{{ $s['remainingMonths'] !== null ? $s['remainingMonths'] : '—' }}</span>
                    </div>

                    <div class="flex items-center justify-between gap-4">
                        <span class="text-zinc-600 dark:text-zinc-300">{{ __('Suggested monthly') }}</span>
                        <span class="font-semibold tabular-nums text-emerald-700 dark:text-emerald-400">
                            {{ $s['suggested'] !== null ? number_format($s['suggested'], 2, ',', '.') : '—' }}
                        </span>
                    </div>
                </div>

                <flux:separator class="my-6" />

                <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('Tip: if you are behind the expected amount, the suggested monthly value will help you catch up based on the remaining time.') }}
                </flux:text>
            </div>
        </div>
    </div>

    <flux:modal wire:model="showEditGoalModal" class="md:w-lg">
        <form wire:submit="saveGoal" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Edit goal') }}</flux:heading>
            </div>

            <flux:field>
                <flux:label>{{ __('Goal name') }}</flux:label>
                <flux:input wire:model="edit_name" required />
                <flux:error name="edit_name" />
            </flux:field>

            <div class="grid gap-4 sm:grid-cols-2">
                <flux:field>
                    <flux:label>{{ __('Target amount') }}</flux:label>
                    <flux:input type="number" step="0.01" min="0" wire:model="edit_target_amount" required />
                    <flux:error name="edit_target_amount" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Start date') }}</flux:label>
                    <flux:input type="date" wire:model="edit_start_date" required />
                    <flux:error name="edit_start_date" />
                </flux:field>
            </div>

            <flux:field>
                <flux:label>{{ __('Target date (optional)') }}</flux:label>
                <flux:input type="date" wire:model="edit_target_date" />
                <flux:error name="edit_target_date" />
            </flux:field>

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost" type="button">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" type="submit">{{ __('Save') }}</flux:button>
            </div>
        </form>
    </flux:modal>

    <flux:modal wire:model="showEditContributionModal" class="md:w-lg">
        <form wire:submit="saveContribution" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Edit contribution') }}</flux:heading>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <flux:field>
                    <flux:label>{{ __('Amount') }}</flux:label>
                    <flux:input type="number" step="0.01" min="0" wire:model="edit_amount" required />
                    <flux:error name="edit_amount" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Date') }}</flux:label>
                    <flux:input type="date" wire:model="edit_date" required />
                    <flux:error name="edit_date" />
                </flux:field>
            </div>

            <flux:field>
                <flux:label>{{ __('Note (optional)') }}</flux:label>
                <flux:input wire:model="edit_note" />
                <flux:error name="edit_note" />
            </flux:field>

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost" type="button">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" type="submit">{{ __('Save') }}</flux:button>
            </div>
        </form>
    </flux:modal>
</div>

