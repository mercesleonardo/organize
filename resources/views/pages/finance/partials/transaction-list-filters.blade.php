@props([
    'categories',
    'statusPaidLabel',
])

<div class="flex flex-wrap items-end gap-3">
    <flux:field class="min-w-[140px] max-w-xs">
        <flux:label>{{ __('Status') }}</flux:label>
        <flux:select wire:model.live="statusFilter">
            <flux:select.option value="">{{ __('All') }}</flux:select.option>
            <flux:select.option value="pending">{{ __('Pendente') }}</flux:select.option>
            <flux:select.option value="paid">{{ $statusPaidLabel }}</flux:select.option>
        </flux:select>
    </flux:field>
    <flux:field class="min-w-[160px] max-w-sm">
        <flux:label>{{ __('Category') }}</flux:label>
        <flux:select wire:model.live="categoryFilter">
            <flux:select.option value="">{{ __('All categories') }}</flux:select.option>
            @foreach ($categories as $cat)
                <flux:select.option :value="$cat->id">{{ $cat->label() }}</flux:select.option>
            @endforeach
        </flux:select>
    </flux:field>
    <flux:field class="min-w-[200px] max-w-md flex-1">
        <flux:label>{{ __('Search description') }}</flux:label>
        <flux:input type="search" wire:model.live.debounce.300ms="search" :placeholder="__('Type to search…')" />
    </flux:field>
    <flux:button type="button" variant="ghost" size="sm" wire:click="clearListFilters">
        {{ __('Clear filters') }}
    </flux:button>
</div>
