<?php

namespace App\Http\Requests;

use App\Enums\{TransactionStatus, TransactionType};
use App\Models\Transaction;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $transaction = $this->route('transaction');

        return $transaction instanceof Transaction && $this->user()->can('update', $transaction);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return self::rulesForEdit();
    }

    /**
     * Regras para o modal de edição no Livewire (prefixo edit_*).
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public static function rulesForEdit(): array
    {
        return [
            'edit_category_id' => [
                'required',
                'integer',
                Rule::exists('categories', 'id')->where('user_id', auth()->id()),
            ],
            'edit_description' => ['required', 'string', 'max:255'],
            'edit_amount'      => ['required', 'numeric', 'min:0.01', 'max:999999999.99'],
            'edit_date'        => ['required', 'date'],
            'edit_type'        => ['required', Rule::enum(TransactionType::class)],
            'edit_status'      => ['required', Rule::enum(TransactionStatus::class)],
        ];
    }
}
