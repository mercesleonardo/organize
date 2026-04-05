<?php

namespace App\Http\Requests;

use App\Enums\TransactionType;
use App\Models\Category;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        $category = $this->route('category');

        return $category instanceof Category && $this->user()->can('update', $category);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $category = $this->route('category');

        if (!$category instanceof Category) {
            return [];
        }

        $type = $this->string('type')->toString();

        return self::rulesFor($category, $type);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public static function rulesFor(Category $category, string $type): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories')
                    ->where('user_id', $category->user_id)
                    ->where('type', $type)
                    ->ignore($category->id),
            ],
            'icon'  => ['nullable', 'string', 'max:100'],
            'color' => ['nullable', 'string', 'max:32'],
            'type'  => ['required', Rule::enum(TransactionType::class)],
        ];
    }
}
