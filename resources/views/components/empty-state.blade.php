@props([
    'message',
])

<div {{ $attributes->class('p-8 text-center text-sm text-zinc-500 dark:text-zinc-400') }}>
    {{ $message }}

    @if (! $slot->isEmpty())
        <div class="mt-4 flex justify-center">
            {{ $slot }}
        </div>
    @endif
</div>

