<?php

namespace App\Actions\Finance;

use App\Enums\TransactionType;
use App\Models\Category;
use Illuminate\Support\Facades\DB;

final class EnsureGlobalDefaultCategoriesAction
{
    /**
     * Garante as categorias de plataforma (despesa e receita) usadas por todos os utilizadores.
     *
     * Os nomes gravados na base são chaves em inglês (locale forçado a `en` durante o seed).
     * Use {@see Category::label()} na interface para respeitar o ficheiro de tradução.
     *
     * Campo {@see \App\Models\Category::$icon}:
     * — nome simples (ex.: `shopping-cart`): ícone Flux / Heroicons publicado na app;
     * — `lucide:nome-do-icone` (ex.: `lucide:carrot`): ícone da biblioteca Lucide via Blade Icons.
     *
     * Campo {@see \App\Models\Category::$color}: hexadecimal `#RRGGBB` para gráficos e barras.
     */
    public function execute(): void
    {
        $previousLocale = app()->getLocale();
        app()->setLocale('en');

        try {
            DB::transaction(function (): void {
                foreach (self::expenseTemplates() as $row) {
                    Category::query()->firstOrCreate(
                        [
                            'name' => __($row['label']),
                            'type' => TransactionType::Expense,
                        ],
                        [
                            'user_id' => null,
                            'icon'    => $row['icon'],
                            'color'   => $row['color'],
                        ],
                    );
                }

                foreach (self::incomeTemplates() as $row) {
                    Category::query()->firstOrCreate(
                        [
                            'name' => __($row['label']),
                            'type' => TransactionType::Income,
                        ],
                        [
                            'user_id' => null,
                            'icon'    => $row['icon'],
                            'color'   => $row['color'],
                        ],
                    );
                }
            });
        } finally {
            app()->setLocale($previousLocale);
        }
    }

    /**
     * @return list<array{label: string, icon: string, color: string}>
     */
    private static function expenseTemplates(): array
    {
        return [
            ['label' => 'Housing', 'icon' => 'home', 'color' => '#6366f1'],
            ['label' => 'Food', 'icon' => 'lucide:utensils-crossed', 'color' => '#ea580c'],
            ['label' => 'Transport', 'icon' => 'lucide:bus', 'color' => '#2563eb'],
            ['label' => 'Health and wellness', 'icon' => 'shield-check', 'color' => '#dc2626'],
            ['label' => 'Leisure and lifestyle', 'icon' => 'sparkles', 'color' => '#a855f7'],
            ['label' => 'Education', 'icon' => 'book-open', 'color' => '#0891b2'],
            ['label' => 'Financial', 'icon' => 'currency-dollar', 'color' => '#059669'],
            ['label' => 'Vehicle', 'icon' => 'lucide:car', 'color' => '#475569'],
            ['label' => 'Pets', 'icon' => 'lucide:dog', 'color' => '#d97706'],
            ['label' => 'Sports', 'icon' => 'trophy', 'color' => '#16a34a'],
            ['label' => 'Clothing', 'icon' => 'shirt', 'color' => '#8b5cf6'],
            ['label' => 'Entertainment', 'icon' => 'film', 'color' => '#0284c7'],
            ['label' => 'Travel', 'icon' => 'globe-americas', 'color' => '#16a34a'],
            ['label' => 'Credit card', 'icon' => 'credit-card', 'color' => '#0284c7'],
            ['label' => 'Other', 'icon' => 'ellipsis-horizontal', 'color' => '#78716c'],
        ];
    }

    /**
     * @return list<array{label: string, icon: string, color: string}>
     */
    private static function incomeTemplates(): array
    {
        return [
            ['label' => 'Salary', 'icon' => 'banknotes', 'color' => '#22c55e'],
            ['label' => 'Freelance / Projects', 'icon' => 'briefcase', 'color' => '#7c3aed'],
            ['label' => 'Investment', 'icon' => 'chart-bar', 'color' => '#0d9488'],
            ['label' => 'Gifts and awards', 'icon' => 'gift', 'color' => '#db2777'],
            ['label' => 'Refunds', 'icon' => 'arrow-uturn-left', 'color' => '#0284c7'],
            ['label' => 'Sales', 'icon' => 'shopping-cart', 'color' => '#ea580c'],
            ['label' => 'Other income', 'icon' => 'ellipsis-horizontal', 'color' => '#78716c'],
        ];
    }
}
