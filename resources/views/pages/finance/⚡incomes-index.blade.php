<?php

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Http\Requests\UpdateTransactionRequest;
use App\Models\Category;
use App\Models\Transaction;
use App\Support\FinancePeriodSummary;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Receitas')] class extends Component {
    use WithPagination;

    public string $monthFilter = '';

    public string $statusFilter = '';

    public string $categoryFilter = '';

    public string $search = '';

    public bool $showEditModal = false;

    public ?int $editingId = null;

    public string $edit_description = '';

    public string $edit_amount = '';

    public string $edit_date = '';

    public string $edit_type = 'income';

    public string $edit_status = '';

    public ?int $edit_category_id = null;

    public int $edit_installment_number = 1;

    public int $edit_total_installments = 1;

    public function mount(): void
    {
        $this->monthFilter = now()->format('Y-m');
    }

    public function updatedMonthFilter(): void
    {
        $this->resetPage();
        unset($this->summary);
    }

    public function clearMonthFilter(): void
    {
        $this->monthFilter = '';
        $this->resetPage();
        unset($this->summary);
    }

    public function useCurrentMonthFilter(): void
    {
        $this->monthFilter = now()->format('Y-m');
        $this->resetPage();
        unset($this->summary);
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
        unset($this->transactions);
    }

    public function updatedCategoryFilter(): void
    {
        $this->resetPage();
        unset($this->transactions);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
        unset($this->transactions);
    }

    public function clearListFilters(): void
    {
        $this->statusFilter = '';
        $this->categoryFilter = '';
        $this->search = '';
        $this->resetPage();
        unset($this->transactions);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, Category>
     */
    public function getCategoriesForEditProperty()
    {
        return Auth::user()
            ->categories()
            ->where('type', TransactionType::Income)
            ->orderBy('name')
            ->get();
    }

    /**
     * @return array{expenses: array{paid: float, pending: float, total: float}, incomes: array{paid: float, pending: float, total: float}, net: float}
     */
    public function getSummaryProperty(): array
    {
        return FinancePeriodSummary::forPeriod(Auth::user(), $this->monthFilter);
    }

    public function getTransactionsProperty(): LengthAwarePaginator
    {
        $query = Auth::user()
            ->transactions()
            ->with('category')
            ->where('type', TransactionType::Income);

        if ($this->monthFilter !== '') {
            [$year, $month] = explode('-', $this->monthFilter);
            $query->whereYear('date', (int) $year)->whereMonth('date', (int) $month);
        }

        if ($this->statusFilter === TransactionStatus::Paid->value) {
            $query->where('status', TransactionStatus::Paid);
        } elseif ($this->statusFilter === TransactionStatus::Pending->value) {
            $query->where('status', TransactionStatus::Pending);
        }

        if ($this->categoryFilter !== '') {
            $query->where('category_id', (int) $this->categoryFilter);
        }

        $term = trim($this->search);
        if ($term !== '') {
            $query->where('description', 'like', '%'.$term.'%');
        }

        return $query
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->paginate(15);
    }

    public function markAsReceived(int $transactionId): void
    {
        $transaction = Transaction::query()
            ->whereKey($transactionId)
            ->whereBelongsTo(Auth::user())
            ->where('type', TransactionType::Income)
            ->firstOrFail();

        $this->authorize('update', $transaction);

        if ($transaction->status !== TransactionStatus::Pending) {
            return;
        }

        $transaction->update(['status' => TransactionStatus::Paid]);

        unset($this->transactions, $this->summary);
    }

    public function openEdit(int $transactionId): void
    {
        $transaction = Transaction::query()
            ->whereKey($transactionId)
            ->whereBelongsTo(Auth::user())
            ->where('type', TransactionType::Income)
            ->firstOrFail();

        $this->authorize('update', $transaction);

        $this->editingId = $transaction->id;
        $this->edit_description = $transaction->description;
        $this->edit_amount = (string) $transaction->amount;
        $this->edit_date = $transaction->date->format('Y-m-d');
        $this->edit_type = TransactionType::Income->value;
        $this->edit_status = $transaction->status->value;
        $this->edit_category_id = $transaction->category_id;
        $this->edit_installment_number = $transaction->installment_number;
        $this->edit_total_installments = $transaction->total_installments;
        $this->showEditModal = true;
    }

    public function saveEdit(): void
    {
        $transaction = Transaction::query()
            ->whereKey($this->editingId)
            ->whereBelongsTo(Auth::user())
            ->where('type', TransactionType::Income)
            ->firstOrFail();

        $this->authorize('update', $transaction);

        $this->validate(UpdateTransactionRequest::rulesForEdit());

        $category = Category::query()
            ->whereKey($this->edit_category_id)
            ->whereBelongsTo(Auth::user())
            ->firstOrFail();

        if ($category->type !== TransactionType::Income) {
            throw ValidationException::withMessages([
                'edit_category_id' => __('Escolha uma categoria de receita.'),
            ]);
        }

        $transaction->update([
            'category_id' => $this->edit_category_id,
            'description' => $this->edit_description,
            'amount' => number_format((float) $this->edit_amount, 2, '.', ''),
            'date' => $this->edit_date,
            'type' => TransactionType::Income,
            'status' => TransactionStatus::from($this->edit_status),
        ]);

        $this->showEditModal = false;
        unset($this->transactions, $this->summary);
    }

    public function delete(int $transactionId): void
    {
        $transaction = Transaction::query()
            ->whereKey($transactionId)
            ->whereBelongsTo(Auth::user())
            ->where('type', TransactionType::Income)
            ->firstOrFail();

        $this->authorize('delete', $transaction);

        $transaction->delete();

        unset($this->transactions, $this->summary);
    }
}; ?>

<div>
    <div class="flex flex-col gap-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <flux:heading size="xl">{{ __('Receitas') }}</flux:heading>
                <flux:text class="mt-1">{{ __('Suas entradas de dinheiro e parcelas futuras.') }}</flux:text>
            </div>
            <flux:button variant="primary" :href="route('finance.incomes.create')" icon="plus" wire:navigate>
                {{ __('Nova receita') }}
            </flux:button>
        </div>

        <div class="flex flex-wrap items-end gap-3">
            <flux:field class="max-w-xs">
                <flux:label>{{ __('Mês') }}</flux:label>
                <flux:input type="month" wire:model.live="monthFilter" />
            </flux:field>
            <flux:button type="button" variant="ghost" size="sm" wire:click="clearMonthFilter">
                {{ __('Todos os períodos') }}
            </flux:button>
            <flux:button type="button" variant="ghost" size="sm" wire:click="useCurrentMonthFilter">
                {{ __('Mês atual') }}
            </flux:button>
        </div>

        @include('pages.finance.partials.transaction-list-filters', [
            'categories' => $this->categoriesForEdit,
            'statusPaidLabel' => __('Recebido'),
        ])

        @include('pages.finance.partials.period-summary', ['summary' => $this->summary])

        <div class="overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700">
            <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                <thead class="bg-zinc-50 dark:bg-zinc-900/50">
                    <tr>
                        <th class="px-4 py-3 text-start text-xs font-medium uppercase text-zinc-500">{{ __('Data') }}</th>
                        <th class="px-4 py-3 text-start text-xs font-medium uppercase text-zinc-500">{{ __('Descrição') }}</th>
                        <th class="px-4 py-3 text-start text-xs font-medium uppercase text-zinc-500">{{ __('Categoria') }}</th>
                        <th class="px-4 py-3 text-start text-xs font-medium uppercase text-zinc-500">{{ __('Recebimento') }}</th>
                        <th class="px-4 py-3 text-end text-xs font-medium uppercase text-zinc-500">{{ __('Valor') }}</th>
                        <th class="px-4 py-3 text-center text-xs font-medium uppercase text-zinc-500">{{ __('Parcela') }}</th>
                        <th class="px-4 py-3 text-end text-xs font-medium uppercase text-zinc-500">{{ __('Ações') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 bg-white dark:divide-zinc-700 dark:bg-zinc-800">
                    @forelse ($this->transactions as $transaction)
                        <tr wire:key="inc-{{ $transaction->id }}">
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-zinc-700 dark:text-zinc-200">
                                {{ $transaction->date->format('d/m/Y') }}
                            </td>
                            <td class="px-4 py-3 text-sm text-zinc-900 dark:text-zinc-100">
                                {{ $transaction->description }}
                            </td>
                            <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-300">
                                {{ $transaction->category?->name ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <flux:badge size="sm" inset="top bottom" :color="$transaction->status === \App\Enums\TransactionStatus::Paid ? 'green' : 'amber'">
                                    {{ $transaction->status->label(\App\Enums\TransactionType::Income) }}
                                </flux:badge>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-end text-sm font-medium tabular-nums text-zinc-900 dark:text-zinc-100">
                                {{ number_format((float) $transaction->amount, 2, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-center text-sm text-zinc-600 dark:text-zinc-400">
                                {{ $transaction->installment_number }}/{{ $transaction->total_installments }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-end text-sm">
                                @if ($transaction->status === \App\Enums\TransactionStatus::Pending)
                                    <flux:button size="sm" variant="primary" wire:click="markAsReceived({{ $transaction->id }})">
                                        {{ __('Marcar como recebido') }}
                                    </flux:button>
                                @endif
                                <flux:button size="sm" variant="ghost" wire:click="openEdit({{ $transaction->id }})">
                                    {{ __('Editar') }}
                                </flux:button>
                                <flux:button
                                    size="sm"
                                    variant="danger"
                                    wire:click="delete({{ $transaction->id }})"
                                    wire:confirm="{{ __('Excluir esta receita? Se for a parcela mestre, as demais parcelas também serão removidas.') }}"
                                >
                                    {{ __('Excluir') }}
                                </flux:button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-sm text-zinc-500">
                                {{ __('Nenhuma receita neste período.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($this->transactions->hasPages())
            <div class="flex justify-center">
                {{ $this->transactions->links() }}
            </div>
        @endif
    </div>

    <flux:modal wire:model="showEditModal" class="md:w-lg">
        <form wire:submit="saveEdit" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Editar receita') }}</flux:heading>
                @if ($edit_total_installments > 1)
                    <flux:text class="mt-2">
                        {{ __('Parcela :n de :total (estrutura de parcelas não é alterada aqui).', ['n' => $edit_installment_number, 'total' => $edit_total_installments]) }}
                    </flux:text>
                @endif
            </div>

            <flux:field>
                <flux:label>{{ __('Categoria') }}</flux:label>
                <flux:select wire:model="edit_category_id" :placeholder="__('Selecione…')" :disabled="$this->categoriesForEdit->isEmpty()">
                    @foreach ($this->categoriesForEdit as $cat)
                        <flux:select.option :value="$cat->id">{{ $cat->name }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:error name="edit_category_id" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('Descrição') }}</flux:label>
                <flux:input wire:model="edit_description" required />
                <flux:error name="edit_description" />
            </flux:field>

            <div class="grid gap-4 sm:grid-cols-2">
                <flux:field>
                    <flux:label>{{ __('Valor') }}</flux:label>
                    <flux:input type="number" step="0.01" min="0" wire:model="edit_amount" required />
                    <flux:error name="edit_amount" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('Data') }}</flux:label>
                    <flux:input type="date" wire:model="edit_date" required />
                    <flux:error name="edit_date" />
                </flux:field>
            </div>

            <flux:radio.group wire:model="edit_status" :label="__('Recebimento')">
                <flux:radio value="pending" :label="__('Pendente')" />
                <flux:radio value="paid" :label="__('Recebido')" />
            </flux:radio.group>
            <flux:error name="edit_status" />

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost" type="button">{{ __('Cancelar') }}</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" type="submit">{{ __('Salvar') }}</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
