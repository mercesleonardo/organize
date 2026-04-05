@props([
    'categories',
    'statusPaidLabel',
])

<div class="flex flex-wrap items-end gap-3">
    <flux:field class="min-w-[140px] max-w-xs">
        <flux:label>{{ __('Status') }}</flux:label>
        <flux:select wire:model.live="statusFilter">
            <flux:select.option value="">{{ __('Todos') }}</flux:select.option>
            <flux:select.option value="pending">{{ __('Pendente') }}</flux:select.option>
            <flux:select.option value="paid">{{ $statusPaidLabel }}</flux:select.option>
        </flux:select>
    </flux:field>
    <flux:field class="min-w-[160px] max-w-sm">
        <flux:label>{{ __('Categoria') }}</flux:label>
        <flux:select wire:model.live="categoryFilter">
            <flux:select.option value="">{{ __('Todas') }}</flux:select.option>
            @foreach ($categories as $cat)
                <flux:select.option :value="$cat->id">{{ $cat->name }}</flux:select.option>
            @endforeach
        </flux:select>
    </flux:field>
    <flux:field class="min-w-[200px] max-w-md flex-1">
        <flux:label>{{ __('Buscar na descrição') }}</flux:label>
        <flux:input type="search" wire:model.live.debounce.300ms="search" :placeholder="__('Digite…')" />
    </flux:field>
    <flux:button type="button" variant="ghost" size="sm" wire:click="clearListFilters">
        {{ __('Limpar filtros') }}
    </flux:button>
</div>
