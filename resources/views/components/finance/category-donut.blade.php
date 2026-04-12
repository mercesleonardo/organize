@props([
    /** @var array{total: float, segments: list<array{color: string, percent: float}>} $breakdown */
    'breakdown',
])

@php
    $gradient = \App\Support\FinanceCategoryBreakdown::conicGradientStops($breakdown['segments']);
    $total      = $breakdown['total'];
@endphp

<div class="relative mx-auto size-44 shrink-0">
    <div
        class="absolute inset-0 rounded-full shadow-inner ring-1 ring-zinc-200/80 dark:ring-zinc-600/80"
        style="background: {{ $gradient }}"
        role="img"
        aria-label="{{ __('Category distribution chart') }}"
    ></div>
    <div
        class="absolute inset-[22%] flex flex-col items-center justify-center rounded-full bg-white text-center dark:bg-zinc-800"
    >
        <span class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">{{ __('Total') }}</span>
        <span class="text-lg font-semibold tabular-nums text-zinc-900 dark:text-zinc-100">
            {{ number_format($total, 2, ',', '.') }}
        </span>
    </div>
</div>
