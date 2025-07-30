<?php

namespace App\Http\Requests\Project;

use App\Models\Project;
use App\Models\Team;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProjectRequest extends FormRequest
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
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        $userId = auth()->id();
        return [
            'name' => ['required', 'string','max:30'],
            'key' => ['required', 'string',
                Rule::unique('projects', 'key')
                    ->where(function ($query) use ($userId,) {
                        $query->where('user_id', '=', $userId);
                    })],
            'description' => ['nullable', 'string'],
            'icon' => ['image', 'mimes:jpg,jpeg,png', 'max:5120'],
            'user_id' => ['required']
        ];
    }

    public function prepareForValidation(): void
    {
        $this->merge([
            'user_id' => auth()->id()
        ]);
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Project name is required.',
            'name.min' => 'Project name must be more than 3 characters.',
            'key.unique' => 'The project key must be unique among your projects.',
            'icon.mimes' => 'Invalid image format! Allowed extensions: .png, .jpg, .jpeg.',
            'icon.max' => 'The maximum allowed image size for uploads is 5MB.',
        ];
    }
}
