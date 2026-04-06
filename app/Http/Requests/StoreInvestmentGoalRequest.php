<?php

namespace App\Http\Requests;

use App\Models\InvestmentGoal;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreInvestmentGoalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', InvestmentGoal::class);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'          => ['required', 'string', 'max:255'],
            'target_amount' => ['required', 'numeric', 'min:0.01', 'max:999999999.99'],
            'start_date'    => ['required', 'date'],
            'target_date'   => ['nullable', 'date', 'after_or_equal:start_date'],
        ];
    }
}
