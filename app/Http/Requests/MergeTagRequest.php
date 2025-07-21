<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MergeTagsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'from_tags' => 'required|array|min:1',
            'from_tags.*' => 'exists:tags,id',
            'to_tag' => 'required|string|max:100'
        ];
    }

    public function messages()
    {
        return [
            'from_tags.required' => 'Select at least 1 tag to merge',
            'from_tags.*.exists' => 'The selected tag is invalid',
            'to_tag.required' => 'Destination tag name is required',
            'to_tag.max' => 'Destination tag name must not exceed 100 characters'
        ];
    }
}
