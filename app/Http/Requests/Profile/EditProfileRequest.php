<?php

namespace App\Http\Requests\Profile;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Validator;

class EditProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        Validator::extend('two_words', function ($attribute, $value) {
            return is_string($value) && preg_match('/^\S+(?:\s\S+)?$/', $value);
        });

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
        return [
            'name' => ['required', 'string', 'min:4', 'max:19', 'two_words'],
            'photo' => ['image', 'mimes:jpg,jpeg,png', 'max:5120', 'nullable'],
            'job_title' => ['string', 'nullable'],
            'phone' => ['string', 'nullable'],
            'location' => ['string', 'nullable'],
            'skills' => ['array', 'max:10', 'nullable'],
            'user_id' => ['required'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'name is required.',
            'name.min' => 'Name must be more than 3 characters.',
            'name.max' => "Name can't be more than 20 characters.",
            'name.two_words' => "Name can't be more than 2 words.",
            'photo.mimes' => 'Invalid image format! Allowed extensions: .png, .jpg, .jpeg.',
            'photo.max' => 'The maximum allowed image size for uploads is 5MB.',];
    }

    public function prepareForValidation()
    {
        $skills = json_decode($this->input('skills'));
        $this->merge([
            'user_id' => auth()->id(),
            'identify_number' => request()->segment(3),
            'skills' => $skills,
        ]);
    }

}
