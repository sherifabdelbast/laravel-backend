<?php

namespace App\Http\Requests\Auth;

use App\Models\RequestHistory;
use Illuminate\Contracts\Validation\ValidationRule as ValidationRuleAlias;
use Illuminate\Foundation\Http\FormRequest;

class CheckIdentifyNumberRequest extends FormRequest
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
     * @return array<string, ValidationRuleAlias|array|string>
     */
    public function rules(): array
    {
        return [
            'identify_number' => ['required', 'string']
        ];
    }
}
