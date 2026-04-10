<?php

namespace App\Actions\Finance;

use App\Enums\TransactionType;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class CreateDefaultCategoriesForUserAction
{
    /**
     * Cria categorias de despesa e receita iniciais para um utilizador recém-registado.
     */
    public function execute(User $user): void
    {
        DB::transaction(function () use ($user): void {
            foreach (self::expenseTemplates() as $row) {
                $user->categories()->create([
                    'name'  => __($row['label']),
                    'icon'  => $row['icon'],
                    'color' => null,
                    'type'  => TransactionType::Expense,
                ]);
            }

            foreach (self::incomeTemplates() as $row) {
                $user->categories()->create([
                    'name'  => __($row['label']),
                    'icon'  => $row['icon'],
                    'color' => null,
                    'type'  => TransactionType::Income,
                ]);
            }
        });
    }

    /**
     * @return list<array{label: string, icon: string}>
     */
    private static function expenseTemplates(): array
    {
        return [
            ['label' => 'Shopping', 'icon' => 'shopping-cart'],
            ['label' => 'Food', 'icon' => 'cake'],
            ['label' => 'Phone', 'icon' => 'device-phone-mobile'],
            ['label' => 'Entertainment', 'icon' => 'microphone'],
            ['label' => 'Education', 'icon' => 'book-open'],
            ['label' => 'Beauty', 'icon' => 'sparkles'],
            ['label' => 'Sports', 'icon' => 'trophy'],
            ['label' => 'Social', 'icon' => 'user-group'],
            ['label' => 'Transport', 'icon' => 'truck'],
            ['label' => 'Clothing', 'icon' => 'shopping-bag'],
            ['label' => 'Car', 'icon' => 'rectangle-stack'],
            ['label' => 'Wine', 'icon' => 'beaker'],
            ['label' => 'Tobacco', 'icon' => 'minus-circle'],
            ['label' => 'Electronics', 'icon' => 'cpu-chip'],
            ['label' => 'Travel', 'icon' => 'paper-airplane'],
            ['label' => 'Health', 'icon' => 'shield-check'],
            ['label' => 'Pets', 'icon' => 'heart'],
            ['label' => 'Repairs', 'icon' => 'wrench-screwdriver'],
            ['label' => 'Housing', 'icon' => 'paint-brush'],
            ['label' => 'Home', 'icon' => 'home'],
            ['label' => 'Gifts', 'icon' => 'gift'],
            ['label' => 'Donations', 'icon' => 'hand-raised'],
            ['label' => 'Lottery', 'icon' => 'ticket'],
            ['label' => 'Snacks', 'icon' => 'cube'],
            ['label' => 'Baby', 'icon' => 'face-smile'],
            ['label' => 'Vegetables', 'icon' => 'leaf'],
            ['label' => 'Fruit', 'icon' => 'star'],
            ['label' => 'Context', 'icon' => 'squares-2x2'],
        ];
    }

    /**
     * @return list<array{label: string, icon: string}>
     */
    private static function incomeTemplates(): array
    {
        return [
            ['label' => 'Salary', 'icon' => 'banknotes'],
            ['label' => 'Freelance', 'icon' => 'briefcase'],
            ['label' => 'Investments', 'icon' => 'chart-bar'],
            ['label' => 'Interest & dividends', 'icon' => 'currency-dollar'],
            ['label' => 'Gifts received', 'icon' => 'gift'],
            ['label' => 'Refunds', 'icon' => 'arrow-uturn-left'],
            ['label' => 'Rental income', 'icon' => 'building-office'],
            ['label' => 'Sales', 'icon' => 'shopping-cart'],
            ['label' => 'Other income', 'icon' => 'ellipsis-horizontal'],
        ];
    }
}
