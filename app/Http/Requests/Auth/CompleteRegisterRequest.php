<?php

namespace App\Http\Requests\Auth;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Validator;

class CompleteRegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        Validator::extend('two_words', function ($attribute, $value) {
            return is_string($value) && preg_match('/^\S+(?:\s\S+)?$/', $value);
        });
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'identify_number' => ['required', 'string'],
            'name' => ['required', 'string', 'min:4', 'max:19', 'two_words'],
            'photo' => ['image', 'mimes:jpg,jpeg,png', 'max:5120'],
            'password' => ['required', 'string', 'min:9',
                'regex:/^(?=.*[a-zA-Z])(?=.*\d)(?!.*\s).+$/', 'confirmed']
        ];
    }

    public function messages(): array
    {
        return
            [
                'name.required' => 'Name is required.',
                'name.min' => 'Name must be more than 3 characters.',
                'name.max' => "Name can't be more than 20 characters.",
                'name.regex' => "Name can't contain numbers or special characters.",
                'name.two_words' => "Name can't be more than 2 words.",
                'password.required' => 'Password is required.',
                'password.min' => 'Password must be more than 8 characters.',
                'password.regex' => 'Password must contain letters and numbers.',
                'password.confirmed' => "Confirm password doesn't match the password.",
                'photo.mimes' => 'Invalid image format! Allowed extensions: .png, .jpg, .jpeg.',
                'photo.max' => 'The maximum allowed image size for uploads is 5MB.',
            ];
    }
}
