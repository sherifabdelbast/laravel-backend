<?php

namespace App\Http\Requests\Role;

use App\Models\Clipboard;
use App\Models\Project;
use App\Models\Team;
use http\Message;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRoleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $userId = auth()->id();
        $projectId = $this->input('project_id');

        $checkIfUserTeamMember = Team::query()
            ->where('project_id', '=', $projectId)
            ->where('user_id', '=', $userId)
            ->accept()
            ->first();

        $checkProjects = Clipboard::query()
            ->where('project_id', '=', $projectId)
            ->archive()
            ->exists();

        if (!$checkProjects && $checkIfUserTeamMember) {
            return true;
        }
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        $projectId = $this->input(['project_id']);
        return [
            'name' => ['required', Rule::unique('roles', 'key')->where(function ($query) use ($projectId) {
                $query->where('project_id', '=', $projectId)
                    ->whereNull('deleted_at');
            })],
            'permissions' => ['array', 'required'],
            'permissions.*' => ['string', Rule::exists('permissions', 'name')],
            'project_id' => ['required'],
            'user_id' => ['required'],
        ];
    }

    public function projectIdInitialization($projectIdentify)
    {
        return Project::query()
            ->where('project_identify', '=', $projectIdentify)
            ->first();
    }

    public function prepareForValidation(): void
    {
        $project = $this->projectIdInitialization(request()->segment(3));
        if ($project) {
            $this->merge([
                'project_id' => $project->id,
                'user_id' => auth()->id(),
            ]);
        }
    }

    public function messages()
    {
        return [
            'name.unique' => 'The name already exists.'
        ];
    }
}
