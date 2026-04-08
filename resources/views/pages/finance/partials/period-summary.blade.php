@php
    $fmt = static fn (float $v): string => 'R$ '.number_format($v, 2, ',', '.');
    $exp = $summary['expenses'];
    $inc = $summary['incomes'];
    $net = $summary['net'];
@endphp

<div class="grid gap-4 md:grid-cols-3">
    <x-finance.summary-card
        :title="__('Expenses in period')"
        :value="$fmt($exp['total'])"
        :rows="[
            ['label' => __('Paid expenses'), 'value' => $fmt($exp['paid']), 'class' => 'text-emerald-600 dark:text-emerald-400'],
            ['label' => __('Investments'), 'value' => $fmt($exp['investmentsPaid'] ?? 0), 'class' => 'text-emerald-700 dark:text-emerald-400'],
            ['label' => __('Outstanding expenses'), 'value' => $fmt($exp['pending']), 'class' => 'text-amber-600 dark:text-amber-400'],
        ]"
    />

    <x-finance.summary-card
        :title="__('Income in period')"
        :value="$fmt($inc['total'])"
        :rows="[
            ['label' => __('Received income'), 'value' => $fmt($inc['paid']), 'class' => 'text-emerald-600 dark:text-emerald-400'],
            ['label' => __('Outstanding income'), 'value' => $fmt($inc['pending']), 'class' => 'text-amber-600 dark:text-amber-400'],
        ]"
    />

    <x-finance.summary-card
        :title="__('Balance (received − paid)')"
        :value="$fmt($net)"
        :value-class="$net >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400'"
        :note="__('Only received income and paid expenses in the period (including investment debits).')"
    />
</div>
