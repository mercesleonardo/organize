<?php

namespace App\Models;

use App\Enums\{TransactionStatus, TransactionType};
use Database\Factories\TransactionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};

#[Fillable([
    'user_id',
    'category_id',
    'description',
    'amount',
    'date',
    'type',
    'status',
    'installment_number',
    'total_installments',
    'parent_id',
])]
class Transaction extends Model
{
    /** @use HasFactory<TransactionFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'date'   => 'date',
            'type'   => TransactionType::class,
            'status' => TransactionStatus::class,
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Transação “mestre” à qual esta parcela pertence (nulo se for a própria mestre ou avulsa).
     *
     * @return BelongsTo<Transaction, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'parent_id');
    }

    /**
     * Parcelas vinculadas a esta transação quando ela é a mestre.
     *
     * @return HasMany<Transaction, $this>
     */
    public function installments(): HasMany
    {
        return $this->hasMany(Transaction::class, 'parent_id');
    }
}
