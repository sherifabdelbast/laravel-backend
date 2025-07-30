<?php

namespace App\Http\Requests\Auth;

use App\Models\RequestHistory;
use Illuminate\Contracts\Validation\ValidationRule as ValidationRuleAlias;
use Illuminate\Foundation\Http\FormRequest;

class CheckTokenRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $token = $this->input('token');
        $checkToken = RequestHistory::query()
            ->where('token', '=', $token)
            ->first();
        if ($checkToken) return true;
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRuleAlias|array|string>
     */
    public function rules(): array
    {
        return [
            'token' => ['required', 'string']
        ];
    }
}
