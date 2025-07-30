<?php

namespace App\Http\Requests\Profile;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ChangePasswordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $userId = auth()->id();
        $identifyNumber = request()->segment(3);

        $user = User::query()
            ->id($userId)
            ->identifyNumber($identifyNumber)
            ->exists();

        if ($user) return true;
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return
            [
                'old_password' => 'required',
                'password' => 'required|string|min:9|regex:/^(?=.*[a-zA-Z])(?=.*\d)(?!.*\s).+$/|confirmed',
                'user_id' => 'required'
            ];
    }

    public function messages(): array
    {
        return [
            'old_password.required' => 'Old password is required.',
            'password.required' => 'password is required.',
            'password.min' => 'Password must be more than 8 characters.',
            'password.confirmed' => "Confirm password doesn't match the password.",
        ];
    }

    public function prepareForValidation()
    {
        $this->merge(
            [
                'user_id' => auth()->id(),
                'identify_number' => request()->segment(3)
            ]);
    }
}
