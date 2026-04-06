@props([
    'title',
    'value',
    'rows' => [],
    'note' => null,
    'valueClass' => 'text-zinc-900 dark:text-zinc-100',
])

<div {{ $attributes->class('rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800') }}>
    <flux:heading size="sm" class="text-zinc-500">{{ $title }}</flux:heading>
    <p class="mt-2 text-2xl font-semibold tabular-nums {{ $valueClass }}">{{ $value }}</p>

    @if (! empty($rows))
        <dl class="mt-3 space-y-1 text-sm text-zinc-600 dark:text-zinc-400">
            @foreach ($rows as $row)
                <div class="flex justify-between gap-2">
                    <dt>{{ $row['label'] }}</dt>
                    <dd class="font-medium tabular-nums {{ $row['class'] ?? '' }}">{{ $row['value'] }}</dd>
                </div>
            @endforeach
        </dl>
    @endif

    @if (filled($note))
        <flux:text class="mt-3 text-sm">{{ $note }}</flux:text>
    @endif
</div>

