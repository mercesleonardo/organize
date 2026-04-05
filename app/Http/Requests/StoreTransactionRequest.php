<?php

namespace App\Http\Requests;

use App\Enums\{TransactionStatus, TransactionType};
use App\Models\Transaction;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Transaction::class);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return self::rulesFor($this->boolean('is_installment'));
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public static function rulesFor(bool $isInstallment): array
    {
        return [
            'category_id' => [
                'required',
                'integer',
                Rule::exists('categories', 'id')->where('user_id', auth()->id()),
            ],
            'description'       => ['required', 'string', 'max:255'],
            'amount'            => ['required', 'numeric', 'min:0.01', 'max:999999999.99'],
            'date'              => ['required', 'date'],
            'type'              => ['required', Rule::enum(TransactionType::class)],
            'status'            => ['required', Rule::enum(TransactionStatus::class)],
            'is_installment'    => ['boolean'],
            'installment_count' => $isInstallment
                ? ['required', 'integer', 'min:2', 'max:120']
                : ['nullable', 'integer'],
        ];
    }
}
