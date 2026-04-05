<?php

namespace App\Http\Requests;

use App\Enums\TransactionType;
use App\Models\Category;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Category::class);
    }

    /**
     * Regras reutilizáveis em Livewire via {@see self::rulesFor()}.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $type = $this->string('type')->toString();

        return self::rulesFor($type);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public static function rulesFor(string $type): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories')
                    ->where('user_id', auth()->id())
                    ->where('type', $type),
            ],
            'icon'  => ['nullable', 'string', 'max:100'],
            'color' => ['nullable', 'string', 'max:32'],
            'type'  => ['required', Rule::enum(TransactionType::class)],
        ];
    }
}
