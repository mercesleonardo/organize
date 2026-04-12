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
                            'color'   => null,
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
                            'color'   => null,
                        ],
                    );
                }
            });
        } finally {
            app()->setLocale($previousLocale);
        }
    }

    /**
     * @return list<array{label: string, icon: string}>
     */
    private static function expenseTemplates(): array
    {
        return [
            ['label' => 'Housing', 'icon' => 'home'],
            ['label' => 'Food', 'icon' => 'lucide:utensils-crossed'],
            ['label' => 'Transport', 'icon' => 'lucide:bus'],
            ['label' => 'Health and wellness', 'icon' => 'shield-check'],
            ['label' => 'Leisure and lifestyle', 'icon' => 'sparkles'],
            ['label' => 'Education', 'icon' => 'book-open'],
            ['label' => 'Financial', 'icon' => 'currency-dollar'],
            ['label' => 'Vehicle', 'icon' => 'lucide:car'],
            ['label' => 'Pets', 'icon' => 'lucide:dog'],
            ['label' => 'Sports', 'icon' => 'trophy'],
        ];
    }

    /**
     * @return list<array{label: string, icon: string}>
     */
    private static function incomeTemplates(): array
    {
        return [
            ['label' => 'Salary', 'icon' => 'banknotes'],
            ['label' => 'Freelance / Projects', 'icon' => 'briefcase'],
            ['label' => 'Investment', 'icon' => 'chart-bar'],
            ['label' => 'Gifts and awards', 'icon' => 'gift'],
            ['label' => 'Refunds', 'icon' => 'arrow-uturn-left'],
            ['label' => 'Sales', 'icon' => 'shopping-cart'],
            ['label' => 'Other income', 'icon' => 'ellipsis-horizontal'],
        ];
    }
}
