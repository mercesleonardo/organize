<?php

use App\Actions\CreateTransactionAction;
use App\Data\CreateTransactionData;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Http\Requests\StoreTransactionRequest;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('New income')] class extends Component {
    public ?int $category_id = null;

    public string $description = '';

    public string $amount = '';

    public string $date = '';

    /** @var string Always income in this form. */
    public string $type = 'income';

    public string $status = '';

    public bool $is_installment = false;

    public int $installment_count = 2;

    public function mount(): void
    {
        $this->date = now()->format('Y-m-d');
        $this->status = TransactionStatus::Pending->value;

        $first = Category::query()->platform()->where('type', TransactionType::Income)->orderBy('name')->first();
        $this->category_id = $first?->id;
    }

    #[Computed]
    public function categories()
    {
        return Category::query()
            ->platform()
            ->where('type', TransactionType::Income)
            ->orderBy('name')
            ->get();
    }

    public function save(): void
    {
        $this->authorize('create', \App\Models\Transaction::class);

        $this->validate(StoreTransactionRequest::rulesFor($this->is_installment));

        $category = Category::query()
            ->whereKey($this->category_id)
            ->whereNull('user_id')
            ->firstOrFail();

        if ($category->type !== TransactionType::Income) {
            throw ValidationException::withMessages([
                'category_id' => __('Please choose an income category.'),
            ]);
        }

        $installmentCount = $this->is_installment ? $this->installment_count : 1;

        app(CreateTransactionAction::class)->execute(new CreateTransactionData(
            user: Auth::user(),
            categoryId: $this->category_id,
            description: $this->description,
            amount: number_format((float) $this->amount, 2, '.', ''),
            date: $this->date,
            type: TransactionType::Income,
            status: TransactionStatus::from($this->status),
            installmentCount: $installmentCount,
        ));

        session()->flash('status', __('Income saved.'));

        $this->reset('description', 'is_installment');
        $this->amount = '';
        $this->is_installment = false;
        $this->installment_count = 2;
        $this->date = now()->format('Y-m-d');
    }
}; ?>

<div>
    <div class="mx-auto flex max-w-2xl flex-col gap-6">
        <div>
            <flux:heading size="xl">{{ __('New income') }}</flux:heading>
            <flux:text class="mt-2">{{ __('Record money coming in. Installments create future entries automatically.') }}</flux:text>
        </div>

        @if (session('status'))
            <flux:callout icon="check-circle" color="green">
                <flux:callout.heading>{{ session('status') }}</flux:callout.heading>
            </flux:callout>
        @endif

        @if ($this->categories->isEmpty())
            <flux:callout icon="exclamation-triangle" color="amber">
                <flux:callout.text>{{ __('No income categories are available yet. Please contact support.') }}</flux:callout.text>
                <x-slot name="actions">
                    <flux:button :href="route('support.contact')" variant="primary" size="sm" wire:navigate>
                        {{ __('Contact us') }}
                    </flux:button>
                </x-slot>
            </flux:callout>
        @endif

        <form wire:submit="save" class="space-y-6">
            <flux:field>
                <flux:label>{{ __('Category') }}</flux:label>
                <flux:select
                    wire:model.live="category_id"
                    :placeholder="__('Select…')"
                    :disabled="$this->categories->isEmpty()"
                >
                    @foreach ($this->categories as $cat)
                        <flux:select.option :value="$cat->id">{{ $cat->label() }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:error name="category_id" />
                @if ($this->category_id)
                    @php
                        $selectedIncomeCategory = $this->categories->firstWhere('id', $this->category_id);
                    @endphp
                    @if ($selectedIncomeCategory)
                        <div
                            class="mt-2 flex items-center gap-2 rounded-lg border border-zinc-200 bg-zinc-50 px-3 py-2 dark:border-zinc-600 dark:bg-zinc-800/50"
                        >
                            <x-category-icon
                                :name="$selectedIncomeCategory->icon"
                                class="size-5 shrink-0 text-zinc-600 dark:text-zinc-300"
                            />
                            <span class="text-sm text-zinc-600 dark:text-zinc-400">{{ $selectedIncomeCategory->label() }}</span>
                        </div>
                    @endif
                @endif
            </flux:field>

            <flux:field>
                <flux:label>{{ __('Description') }}</flux:label>
                <flux:input wire:model="description" :placeholder="__('e.g. Salary')" required />
                <flux:error name="description" />
            </flux:field>

            <div class="grid gap-4 sm:grid-cols-2">
                <flux:field>
                    <flux:label>{{ __('Total amount') }}</flux:label>
                    <flux:input type="number" step="0.01" min="0" wire:model="amount" :placeholder="__('0.00')" required />
                    <flux:error name="amount" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('Date (first installment / single)') }}</flux:label>
                    <flux:input type="date" wire:model="date" required />
                    <flux:error name="date" />
                </flux:field>
            </div>

            <flux:radio.group wire:model="status" :label="__('Status')">
                <flux:radio value="pending" :label="__('Pending receipt')" />
                <flux:radio value="paid" :label="__('Already received')" />
            </flux:radio.group>
            <flux:error name="status" />

            <flux:field variant="inline">
                <flux:label>{{ __('Installment plan?') }}</flux:label>
                <flux:switch wire:model.live="is_installment" :label="__('Split into monthly installments')" align="left" />
                <flux:error name="is_installment" />
            </flux:field>

            @if ($is_installment)
                <flux:field>
                    <flux:label>{{ __('Number of installments') }}</flux:label>
                    <flux:input type="number" min="2" max="120" wire:model="installment_count" />
                    <flux:error name="installment_count" />
                </flux:field>
            @endif

            <div class="flex justify-end gap-2">
                <flux:button variant="primary" type="submit" :disabled="$this->categories->isEmpty()">
                    {{ __('Save income') }}
                </flux:button>
            </div>
        </form>
    </div>
</div>
