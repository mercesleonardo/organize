<?php

namespace App\Actions;

use App\Data\CreateTransactionData;
use App\Enums\{TransactionStatus, TransactionType};
use App\Models\{Category, Transaction};
use App\Support\ExpensePaidBalance;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final class CreateTransactionAction
{
    public function __construct(
        private GenerateInstallmentsAction $generateInstallments,
    ) {
    }

    /**
     * Cria uma transação avulsa ou um grupo de parcelas ligadas à transação mestre.
     *
     * @return Collection<int, Transaction>
     */
    public function execute(CreateTransactionData $data): Collection
    {
        if ($data->installmentCount < 1) {
            throw new InvalidArgumentException('O número de parcelas deve ser pelo menos 1.');
        }

        return DB::transaction(function () use ($data) {
            $category = Category::query()
                ->whereKey($data->categoryId)
                ->firstOrFail();

            if ($category->type !== $data->type) {
                throw new InvalidArgumentException('O tipo da categoria não corresponde ao tipo da transação.');
            }

            if ($data->type === TransactionType::Expense && $data->status === TransactionStatus::Paid) {
                ExpensePaidBalance::assertCanSetExpensePaid(
                    $data->user,
                    TransactionStatus::Paid,
                    (float) $data->amount,
                    null,
                    'status',
                );
            }

            if ($data->installmentCount === 1) {
                return Collection::make([$this->createSingleTransaction($data, $category->id)]);
            }

            $lines = $this->generateInstallments->execute(
                $data->amount,
                Carbon::parse($data->date),
                $data->installmentCount
            );

            $created = new Collection();
            $master  = null;

            foreach ($lines as $line) {
                $attributes = [
                    'user_id'            => $data->user->id,
                    'category_id'        => $category->id,
                    'description'        => $data->description,
                    'amount'             => $line->amount,
                    'date'               => $line->date->format('Y-m-d'),
                    'type'               => $data->type,
                    'status'             => $data->status,
                    'installment_number' => $line->installmentNumber,
                    'total_installments' => $line->totalInstallments,
                    'parent_id'          => null,
                ];

                if ($master === null) {
                    $master = Transaction::query()->create($attributes);
                    $created->push($master);

                    continue;
                }

                $attributes['parent_id'] = $master->id;
                $created->push(Transaction::query()->create($attributes));
            }

            return $created;
        });
    }

    private function createSingleTransaction(CreateTransactionData $data, int $categoryId): Transaction
    {
        return Transaction::query()->create([
            'user_id'            => $data->user->id,
            'category_id'        => $categoryId,
            'description'        => $data->description,
            'amount'             => $data->amount,
            'date'               => $data->date,
            'type'               => $data->type,
            'status'             => $data->status,
            'installment_number' => 1,
            'total_installments' => 1,
            'parent_id'          => null,
        ]);
    }
}
