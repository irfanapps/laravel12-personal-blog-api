<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rules\Password;

class UpdateProfileRequest extends FormRequest
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
        $userId = $this->user()->id;

        return [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $userId,
            'password' => [
                'sometimes',
                'string',
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
            ],
            'password_confirmation' => 'required_with:password|same:password',
            'bio' => 'nullable|string|max:500',
            'avatar' => ['sometimes', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ];
    }

    /**
     * Custom error messages for validation
     */
    public function messages(): array
    {
        return [
            'name.string' => 'Please enter a valid name',
            'name.max' => 'Name is too long (max 255 characters)',

            'email.email' => 'Please enter a valid email',
            'email.max' => 'Email is too long (max 255 characters)',
            'email.unique' => 'This email is already in use',

            'password.string' => 'Please enter a valid password',
            'password.confirmed' => 'Passwords do not match',
            'password.min' => 'Password must be at least 8 characters long',

            'password_confirmation.required_with' => 'Please confirm your password',
            'password_confirmation.same' => 'Passwords must match',

            'bio.string' => 'Please enter a valid bio',
            'bio.max' => 'Bio is too long (max 500 characters)',

            'avatar.image' => 'Please upload an image file',
            'avatar.mimes' => 'Supported formats: JPEG, PNG, JPG, or GIF',
            'avatar.max' => 'Maximum image size is 2MB',
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'status' => false,
                'message' => 'Failed Validation',
                'errors' => $validator->errors()
            ], 422)
        );
    }
}
