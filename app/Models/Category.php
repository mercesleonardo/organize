<?php

namespace App\Models;

use App\Enums\TransactionType;
use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\{Builder, Model};
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};

#[Fillable(['user_id', 'name', 'icon', 'color', 'type'])]
class Category extends Model
{
    /** @use HasFactory<CategoryFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => TransactionType::class,
        ];
    }

    /**
     * Categorias partilhadas pela plataforma (sem dono por utilizador).
     *
     * @param  Builder<Category>  $query
     * @return Builder<Category>
     */
    public function scopePlatform(Builder $query): Builder
    {
        return $query->whereNull('user_id');
    }

    /**
     * Nome para exibição (a coluna {@see $name} guarda a chave em inglês nas categorias de plataforma).
     */
    public function label(): string
    {
        return __($this->name);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<Transaction, $this>
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}
