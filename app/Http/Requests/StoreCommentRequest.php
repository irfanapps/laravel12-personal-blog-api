<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCommentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Diubah sesuai kebutuhan auth
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */ public function rules(): array
    {
        return [
            'content' => [
                'required',
                'string',
                'max:1000',
                function ($attribute, $value, $fail) {
                    $bannedWords = ['spam', 'ads', 'promotion'];
                    foreach ($bannedWords as $word) {
                        if (str_contains(strtolower($value), $word)) {
                            $fail('Comments contain words that are not allowed.');
                            break;
                        }
                    }
                }
            ],
            'post_id' => [
                'required',
                'exists:posts,id'
            ],
            'parent_id' => [
                'nullable',
                'exists:comments,id',
                function ($attribute, $value, $fail) {
                    if ($value) {
                        $parent = \App\Models\Comment::find($value);
                        if ($parent && $parent->parent_id) {
                            $grandParent = \App\Models\Comment::find($parent->parent_id);
                            if ($grandParent && $grandParent->parent_id) {
                                $fail('Comment replies are only allowed up to a maximum of 3 levels.');
                            }
                        }
                    }
                }
            ]
        ];
    }

    /**
     * Custom error messages
     */
    public function messages(): array
    {
        return [
            'content.required' => 'The comment content is required',
            'content.max' => 'The comment may not be longer than 1000 characters',
            'post_id.exists' => 'The post could not be found',
            'parent_id.exists' => 'The parent comment could not be found'
        ];
    }

    /**
     * Prepare data before validation
     */
    protected function prepareForValidation()
    {
        // If parent_id is not included, set it as null
        if (!$this->has('parent_id')) {
            $this->merge(['parent_id' => null]);
        }
    }
}
