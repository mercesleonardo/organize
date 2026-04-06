<?php

use App\Enums\TransactionType;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Categorias')] class extends Component {
    public string $name = '';

    public string $icon = '';

    public string $color = '';

    public string $type = '';

    public ?int $editingId = null;

    public bool $showModal = false;

    public function mount(): void
    {
        $this->type = TransactionType::Expense->value;
    }

    #[Computed]
    public function categories()
    {
        return Auth::user()->categories()->orderBy('name')->get();
    }

    public function openCreate(): void
    {
        $this->authorize('create', Category::class);
        $this->editingId = null;
        $this->name = '';
        $this->icon = '';
        $this->color = '';
        $this->type = TransactionType::Expense->value;
        $this->showModal = true;
    }

    public function openEdit(int $categoryId): void
    {
        $category = Category::query()
            ->whereKey($categoryId)
            ->whereBelongsTo(Auth::user())
            ->firstOrFail();

        $this->authorize('update', $category);

        $this->editingId = $category->id;
        $this->name = $category->name;
        $this->icon = (string) ($category->icon ?? '');
        $this->color = (string) ($category->color ?? '');
        $this->type = $category->type->value;
        $this->showModal = true;
    }

    public function save(): void
    {
        if ($this->editingId) {
            $category = Category::query()
                ->whereKey($this->editingId)
                ->whereBelongsTo(Auth::user())
                ->firstOrFail();

            $this->authorize('update', $category);

            $this->validate(UpdateCategoryRequest::rulesFor($category, $this->type));

            $category->update([
                'name' => $this->name,
                'icon' => $this->icon ?: null,
                'color' => $this->color ?: null,
                'type' => $this->type,
            ]);
        } else {
            $this->authorize('create', Category::class);

            $this->validate(StoreCategoryRequest::rulesFor($this->type));

            Auth::user()->categories()->create([
                'name' => $this->name,
                'icon' => $this->icon ?: null,
                'color' => $this->color ?: null,
                'type' => $this->type,
            ]);
        }

        $this->showModal = false;
        unset($this->categories);
    }

    public function delete(int $categoryId): void
    {
        $category = Category::query()
            ->whereKey($categoryId)
            ->whereBelongsTo(Auth::user())
            ->firstOrFail();

        $this->authorize('delete', $category);

        $category->delete();

        unset($this->categories);
    }
}; ?>

<div>
    <div class="flex flex-col gap-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <flux:heading size="xl">{{ __('Categorias') }}</flux:heading>
            <flux:button variant="primary" wire:click="openCreate" icon="plus">
                {{ __('Nova categoria') }}
            </flux:button>
        </div>

        <div class="overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700">
            {{-- Desktop table --}}
            <table class="hidden min-w-full divide-y divide-zinc-200 dark:divide-zinc-700 md:table">
                <thead class="bg-zinc-50 dark:bg-zinc-900/50">
                    <tr>
                        <th class="px-4 py-3 text-start text-xs font-medium uppercase text-zinc-500">{{ __('Nome') }}</th>
                        <th class="px-4 py-3 text-start text-xs font-medium uppercase text-zinc-500">{{ __('Tipo') }}</th>
                        <th class="px-4 py-3 text-end text-xs font-medium uppercase text-zinc-500">{{ __('Ações') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 bg-white dark:divide-zinc-700 dark:bg-zinc-800">
                    @forelse ($this->categories as $category)
                        <tr wire:key="category-{{ $category->id }}">
                            <td class="px-4 py-3 text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                <div class="flex items-center gap-2">
                                    @if ($category->color)
                                        <span class="size-3 rounded-full border border-zinc-300" style="background-color: {{ $category->color }}"></span>
                                    @endif
                                    {{ $category->name }}
                                </div>
                            </td>
                            <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-300">
                                <flux:badge size="sm" inset="top bottom">
                                    {{ $category->type->label() }}
                                </flux:badge>
                            </td>
                            <td class="px-4 py-3 text-end text-sm">
                                <flux:button size="sm" variant="ghost" wire:click="openEdit({{ $category->id }})">
                                    {{ __('Editar') }}
                                </flux:button>
                                <flux:button
                                    size="sm"
                                    variant="danger"
                                    wire:click="delete({{ $category->id }})"
                                    wire:confirm="{{ __('Excluir esta categoria? Transações vinculadas serão removidas.') }}"
                                >
                                    {{ __('Excluir') }}
                                </flux:button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-4 py-8 text-center text-sm text-zinc-500">
                                {{ __('Nenhuma categoria ainda.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{-- Mobile cards --}}
            <div class="divide-y divide-zinc-200 bg-white dark:divide-zinc-700 dark:bg-zinc-800 md:hidden">
                @forelse ($this->categories as $category)
                    <div wire:key="category-mobile-{{ $category->id }}" class="p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    @if ($category->color)
                                        <span class="mt-1 size-3 shrink-0 rounded-full border border-zinc-300 dark:border-zinc-600" style="background-color: {{ $category->color }}"></span>
                                    @endif
                                    <div class="truncate text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                        {{ $category->name }}
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <flux:badge size="sm" inset="top bottom">
                                        {{ $category->type->label() }}
                                    </flux:badge>
                                </div>
                            </div>
                            <div class="flex shrink-0 items-center gap-2">
                                <x-action-dropdown :title="__('Ações')">
                                    <flux:menu.item icon="pencil" wire:click="openEdit({{ $category->id }})">
                                        {{ __('Editar') }}
                                    </flux:menu.item>
                                    <flux:menu.separator />
                                    <flux:menu.item
                                        icon="trash"
                                        wire:click="delete({{ $category->id }})"
                                        wire:confirm="{{ __('Excluir esta categoria? Transações vinculadas serão removidas.') }}"
                                    >
                                        {{ __('Excluir') }}
                                    </flux:menu.item>
                                </x-action-dropdown>
                            </div>
                        </div>
                    </div>
                @empty
                    <x-empty-state :message="__('Nenhuma categoria ainda.')" />
                @endforelse
            </div>
        </div>
    </div>

    <flux:modal wire:model="showModal" class="md:w-lg">
        <form wire:submit="save" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $this->editingId ? __('Editar categoria') : __('Nova categoria') }}</flux:heading>
                <flux:text class="mt-2">{{ __('Toda transação deve estar em uma categoria.') }}</flux:text>
            </div>

            <flux:field>
                <flux:label>{{ __('Nome') }}</flux:label>
                <flux:input wire:model="name" :placeholder="__('Ex.: Moradia')" required />
                <flux:error name="name" />
            </flux:field>

            <flux:radio.group wire:model.live="type" :label="__('Tipo')">
                <flux:radio value="expense" :label="__('Despesa')" />
                <flux:radio value="income" :label="__('Receita')" />
            </flux:radio.group>
            <flux:error name="type" />

            <div class="grid gap-4 sm:grid-cols-2">
                <flux:field>
                    <flux:label>{{ __('Ícone (opcional)') }}</flux:label>
                    <flux:input wire:model="icon" :placeholder="__('home')" />
                    <flux:error name="icon" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('Cor (hex opcional)') }}</flux:label>
                    <flux:input type="color" wire:model="color" />
                    <flux:error name="color" />
                </flux:field>
            </div>

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost" type="button">{{ __('Cancelar') }}</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" type="submit">{{ __('Salvar') }}</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
