<?php

namespace App\Http\Requests;

use App\Models\InvestmentGoal;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateInvestmentGoalRequest extends FormRequest
{
    public function authorize(): bool
    {
        $goal = $this->route('investmentGoal');

        return $goal instanceof InvestmentGoal && $this->user()->can('update', $goal);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'edit_name'          => ['required', 'string', 'max:255'],
            'edit_target_amount' => ['required', 'numeric', 'min:0.01', 'max:999999999.99'],
            'edit_start_date'    => ['required', 'date'],
            'edit_target_date'   => ['nullable', 'date', 'after_or_equal:edit_start_date'],
        ];
    }
}
