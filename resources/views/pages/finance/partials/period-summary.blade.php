@php
    $fmt = static fn (float $v): string => 'R$ '.number_format($v, 2, ',', '.');
    $exp = $summary['expenses'];
    $inc = $summary['incomes'];
    $net = $summary['net'];
@endphp

<div class="grid gap-4 md:grid-cols-3">
    <x-finance.summary-card
        :title="__('Despesas no período')"
        :value="$fmt($exp['total'])"
        :rows="[
            ['label' => __('Pagas'), 'value' => $fmt($exp['paid']), 'class' => 'text-emerald-600 dark:text-emerald-400'],
            ['label' => __('Investimentos'), 'value' => $fmt($exp['investmentsPaid'] ?? 0), 'class' => 'text-emerald-700 dark:text-emerald-400'],
            ['label' => __('Pendentes'), 'value' => $fmt($exp['pending']), 'class' => 'text-amber-600 dark:text-amber-400'],
        ]"
    />

    <x-finance.summary-card
        :title="__('Receitas no período')"
        :value="$fmt($inc['total'])"
        :rows="[
            ['label' => __('Recebidas'), 'value' => $fmt($inc['paid']), 'class' => 'text-emerald-600 dark:text-emerald-400'],
            ['label' => __('Pendentes'), 'value' => $fmt($inc['pending']), 'class' => 'text-amber-600 dark:text-amber-400'],
        ]"
    />

    <x-finance.summary-card
        :title="__('Saldo (recebidas − pagas)')"
        :value="$fmt($net)"
        :value-class="$net >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400'"
        :note="__('Apenas receitas recebidas e despesas pagas no período (inclui débitos de investimentos).')"
    />
</div>
