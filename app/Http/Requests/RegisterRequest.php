<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class RegisterRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => [
                'required',
                'string',
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
            ],
            'password_confirmation' => 'required|same:password',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Please enter your full name',
            'name.string' => 'Name should only contain letters',
            'name.max' => 'Name is too long (maximum 255 characters)',

            'email.required' => 'Please enter your email',
            'email.string' => 'Email should be text format',
            'email.email' => 'Please enter a valid email',
            'email.max' => 'Email is too long (maximum 255 characters)',
            'email.unique' => 'This email is already in use',

            'password.required' => 'Please create a password',
            'password.string' => 'Password should be text format',
            'password.confirmed' => 'Passwords do not match',
            'password.min' => 'Password too short (minimum 8 characters)',

            'password_confirmation.required' => 'Please confirm your password',
            'password_confirmation.same' => 'Passwords must match exactly',
        ];
    }

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
