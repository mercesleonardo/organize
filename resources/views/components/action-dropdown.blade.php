@props([
    'title' => __('Actions'),
])

<flux:dropdown position="bottom" align="end">
    <flux:button size="sm" variant="ghost" icon="ellipsis-vertical" square :title="$title" />

    <flux:menu>
        {{ $slot }}
    </flux:menu>
</flux:dropdown>

