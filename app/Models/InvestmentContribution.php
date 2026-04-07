<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvestmentContribution extends Model
{
    /** @use HasFactory<\Database\Factories\InvestmentContributionFactory> */
    use HasFactory;

    protected $fillable = [
        'investment_goal_id',
        'user_id',
        'debit_transaction_id',
        'amount',
        'date',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'date'   => 'date',
        ];
    }

    public function goal(): BelongsTo
    {
        return $this->belongsTo(InvestmentGoal::class, 'investment_goal_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function debitTransaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'debit_transaction_id');
    }
}
