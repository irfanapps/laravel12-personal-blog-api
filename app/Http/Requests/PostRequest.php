<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class PostRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'title' => 'required|string|max:255',
            'content' => 'required|string|min:100',
            'excerpt' => 'nullable|string|max:300',
            'category_id' => 'nullable|exists:categories,id',
            'tags' => 'nullable|array',
            'tags.*' => 'string|distinct',
            'is_draft' => 'boolean',
            'featured_image' => [
                'sometimes',
                'file',
                'image',
                'mimes:jpeg,png,jpg,gif,webp',
                'max:2048', // 2MB
                'dimensions:min_width=600,min_height=300',
            ],
            //'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ];

        //Untuk update, slug boleh sama dengan slug sebelumnya
        // if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
        //     $post = $this->route('post');
        //     $rules['title'] = [  
        //         Rule::unique('posts')->ignore($post->id)
        //     ];
        // } else {
        //     $rules['title'][] = 'unique:posts';
        // }

        return $rules;
    }

    public function messages()
    {
        return [
            'title.unique' => 'A post with this title already exists',
            'content.min' => 'The content must be at least 100 characters',
            'featured_image.max' => 'The featured image must not be larger than 2MB'
        ];
    }

    public function validatedData()
    {
        $validated = $this->validated();

        // Hapus fields yang tidak perlu disimpan langsung ke model
        unset($validated['tags']);
        unset($validated['featured_image']);

        // Tambahkan user_id dan published_at jika perlu
        if ($this->isMethod('POST')) {
            $validated['user_id'] = Auth::user()->id;
        }

        if (isset($validated['is_draft']) && !$validated['is_draft']) {
            $validated['published_at'] = now();
        }

        return $validated;
    }
}
