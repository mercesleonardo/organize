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

new #[Title('Nova despesa')] class extends Component {
    public ?int $category_id = null;

    public string $description = '';

    public string $amount = '';

    public string $date = '';

    /** @var 'expense' Sempre despesa neste formulário. */
    public string $type = 'expense';

    public string $status = '';

    public bool $is_installment = false;

    public int $installment_count = 2;

    public function mount(): void
    {
        $this->date = now()->format('Y-m-d');
        $this->status = TransactionStatus::Pending->value;

        $first = Auth::user()->categories()->where('type', TransactionType::Expense)->orderBy('name')->first();
        $this->category_id = $first?->id;
    }

    #[Computed]
    public function categories()
    {
        return Auth::user()
            ->categories()
            ->where('type', TransactionType::Expense)
            ->orderBy('name')
            ->get();
    }

    public function save(): void
    {
        $this->authorize('create', \App\Models\Transaction::class);

        $this->validate(StoreTransactionRequest::rulesFor($this->is_installment));

        $category = Category::query()
            ->whereKey($this->category_id)
            ->whereBelongsTo(Auth::user())
            ->firstOrFail();

        if ($category->type !== TransactionType::Expense) {
            throw ValidationException::withMessages([
                'category_id' => __('Escolha uma categoria de despesa.'),
            ]);
        }

        $installmentCount = $this->is_installment ? $this->installment_count : 1;

        app(CreateTransactionAction::class)->execute(new CreateTransactionData(
            user: Auth::user(),
            categoryId: $this->category_id,
            description: $this->description,
            amount: number_format((float) $this->amount, 2, '.', ''),
            date: $this->date,
            type: TransactionType::Expense,
            status: TransactionStatus::from($this->status),
            installmentCount: $installmentCount,
        ));

        session()->flash('status', __('Despesa registrada.'));

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
            <flux:heading size="xl">{{ __('Nova despesa') }}</flux:heading>
            <flux:text class="mt-2">{{ __('Registre uma saída de dinheiro. Parcelas geram lançamentos futuros automaticamente.') }}</flux:text>
        </div>

        @if (session('status'))
            <flux:callout icon="check-circle" color="green">
                <flux:callout.heading>{{ session('status') }}</flux:callout.heading>
            </flux:callout>
        @endif

        @if ($this->categories->isEmpty())
            <flux:callout icon="exclamation-triangle" color="amber">
                <flux:callout.text>{{ __('Crie uma categoria de despesa antes de lançar.') }}</flux:callout.text>
                <x-slot name="actions">
                    <flux:button :href="route('finance.categories.index')" variant="primary" size="sm" wire:navigate>
                        {{ __('Ir para categorias') }}
                    </flux:button>
                </x-slot>
            </flux:callout>
        @endif

        <form wire:submit="save" class="space-y-6">
            <flux:field>
                <flux:label>{{ __('Categoria') }}</flux:label>
                <flux:select wire:model="category_id" :placeholder="__('Selecione…')" :disabled="$this->categories->isEmpty()">
                    @foreach ($this->categories as $cat)
                        <flux:select.option :value="$cat->id">{{ $cat->name }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:error name="category_id" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('Descrição') }}</flux:label>
                <flux:input wire:model="description" :placeholder="__('Ex.: Supermercado')" required />
                <flux:error name="description" />
            </flux:field>

            <div class="grid gap-4 sm:grid-cols-2">
                <flux:field>
                    <flux:label>{{ __('Valor total') }}</flux:label>
                    <flux:input type="number" step="0.01" min="0" wire:model="amount" :placeholder="__('0,00')" required />
                    <flux:error name="amount" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('Data (1ª parcela / única)') }}</flux:label>
                    <flux:input type="date" wire:model="date" required />
                    <flux:error name="date" />
                </flux:field>
            </div>

            <flux:radio.group wire:model="status" :label="__('Situação')">
                <flux:radio value="pending" :label="__('Pendente de pagamento')" />
                <flux:radio value="paid" :label="__('Já pago')" />
            </flux:radio.group>
            <flux:error name="status" />

            <flux:field variant="inline">
                <flux:label>{{ __('Parcelado?') }}</flux:label>
                <flux:switch wire:model.live="is_installment" :label="__('Dividir em parcelas mensais')" align="left" />
                <flux:error name="is_installment" />
            </flux:field>

            @if ($is_installment)
                <flux:field>
                    <flux:label>{{ __('Número de parcelas') }}</flux:label>
                    <flux:input type="number" min="2" max="120" wire:model="installment_count" />
                    <flux:error name="installment_count" />
                </flux:field>
            @endif

            <div class="flex justify-end gap-2">
                <flux:button variant="primary" type="submit" :disabled="$this->categories->isEmpty()">
                    {{ __('Salvar despesa') }}
                </flux:button>
            </div>
        </form>
    </div>
</div>
