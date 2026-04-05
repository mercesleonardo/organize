@php
    $fmt = static fn (float $v): string => 'R$ '.number_format($v, 2, ',', '.');
    $exp = $summary['expenses'];
    $inc = $summary['incomes'];
    $net = $summary['net'];
@endphp

<div class="grid gap-4 md:grid-cols-3">
    <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800">
        <flux:heading size="sm" class="text-zinc-500">{{ __('Despesas no período') }}</flux:heading>
        <p class="mt-2 text-2xl font-semibold tabular-nums text-zinc-900 dark:text-zinc-100">{{ $fmt($exp['total']) }}</p>
        <dl class="mt-3 space-y-1 text-sm text-zinc-600 dark:text-zinc-400">
            <div class="flex justify-between gap-2">
                <dt>{{ __('Pagas') }}</dt>
                <dd class="font-medium tabular-nums text-emerald-600 dark:text-emerald-400">{{ $fmt($exp['paid']) }}</dd>
            </div>
            <div class="flex justify-between gap-2">
                <dt>{{ __('Pendentes') }}</dt>
                <dd class="font-medium tabular-nums text-amber-600 dark:text-amber-400">{{ $fmt($exp['pending']) }}</dd>
            </div>
        </dl>
    </div>

    <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800">
        <flux:heading size="sm" class="text-zinc-500">{{ __('Receitas no período') }}</flux:heading>
        <p class="mt-2 text-2xl font-semibold tabular-nums text-zinc-900 dark:text-zinc-100">{{ $fmt($inc['total']) }}</p>
        <dl class="mt-3 space-y-1 text-sm text-zinc-600 dark:text-zinc-400">
            <div class="flex justify-between gap-2">
                <dt>{{ __('Recebidas') }}</dt>
                <dd class="font-medium tabular-nums text-emerald-600 dark:text-emerald-400">{{ $fmt($inc['paid']) }}</dd>
            </div>
            <div class="flex justify-between gap-2">
                <dt>{{ __('Pendentes') }}</dt>
                <dd class="font-medium tabular-nums text-amber-600 dark:text-amber-400">{{ $fmt($inc['pending']) }}</dd>
            </div>
        </dl>
    </div>

    <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800">
        <flux:heading size="sm" class="text-zinc-500">{{ __('Saldo (recebidas − pagas)') }}</flux:heading>
        <p @class([
            'mt-2 text-2xl font-semibold tabular-nums',
            'text-emerald-600 dark:text-emerald-400' => $net >= 0,
            'text-rose-600 dark:text-rose-400' => $net < 0,
        ])>{{ $fmt($net) }}</p>
        <flux:text class="mt-3 text-sm">{{ __('Apenas receitas recebidas e despesas pagas no período.') }}</flux:text>
    </div>
</div>
