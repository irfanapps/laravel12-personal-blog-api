<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $rules = [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories')->ignore($this->category)
            ],
            'description' => 'nullable|string|max:500'
        ];

        // Untuk create, tambahkan validasi slug unik
        if ($this->isMethod('POST')) {
            $rules['name'][] = Rule::unique('categories');
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'name.required' => 'The category name is required',
            'name.unique' => 'The category name is already taken',
            'name.max' => 'The category name may not be greater than 255 characters',
            'description.max' => 'The description may not be greater than 500 characters'
        ];
    }

    /**
     * Get validated data including auto-generated slug
     */
    public function validatedData()
    {
        $validated = $this->validated();
        $validated['slug'] = strtolower(str_replace(' ', '-', $validated['name']));
        return $validated;
    }
}
