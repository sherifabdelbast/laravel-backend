<?php

namespace App\Http\Requests\Issue;

use App\Models\Clipboard;
use App\Models\Team;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ListIssueRequest extends FormRequest
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
        return [
            'user_id' => ['required'],
            'project_id' => ['integer', 'nullable'],
            'assignToMe' => ['integer', 'nullable'],
            'search' => ['string', 'nullable'],
            'assignee' => ['integer', 'nullable'],
            'status' => ['string', 'nullable'],
        ];
    }

    public function prepareForValidation()
    {
        $this->merge([
            'user_id' => auth()->id(),
            'project_id' => $this->query('project'),
            'assignToMe' => $this->query('assignToMe'),
            'search' => $this->query('search'),
            'assignee' => $this->query('assignee'),
            'status' => $this->query('status'),
        ]);
    }
}
