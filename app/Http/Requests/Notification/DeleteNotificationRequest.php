<?php

namespace App\Http\Requests\Notification;

use App\Models\Recipient;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class DeleteNotificationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $userId = auth()->id();
        $notifyId = request()->segment(3);

        if (Recipient::query()
            ->where('user_id', '=', $userId)
            ->where('notify_id', '=', $notifyId)
            ->first()) return true;

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
            'user_id' => ['required'],
            'notify_id' => ['required']
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'user_id' => auth()->id(),
            'notify_id' => request()->segment(3)
        ]);
    }
}
