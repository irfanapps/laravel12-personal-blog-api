<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TagRequest extends FormRequest
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
                'max:100',
                Rule::unique('tags')->ignore($this->tag)
            ]
        ];

        // Untuk create, tambahkan validasi unik
        if ($this->isMethod('POST')) {
            $rules['name'][] = Rule::unique('tags');
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'name.required' => 'Please enter a tag name',
            'name.unique' => 'Tag name is already in use',
            'name.max' => 'Maximum tag length is 100 characters'
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
