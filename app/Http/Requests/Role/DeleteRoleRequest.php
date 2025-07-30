<?php

namespace App\Http\Requests\Role;

use App\Models\Clipboard;
use App\Models\Project;
use App\Models\Role;
use App\Models\Team;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DeleteRoleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $userId = auth()->id();
        $projectId = $this->input('project_id');
        $roleId = request()->segment(5);

        $checkIfUserTeamMember = Team::query()
            ->where('project_id', '=', $projectId)
            ->where('user_id', '=', $userId)
            ->accept()
            ->first();

        $checkProjects = Clipboard::query()
            ->where('project_id', '=', $projectId)
            ->archive()
            ->exists();
        $checkRole = Role::query()
            ->where('id', '=', $roleId)
            ->whereNot('key', '=', 'owner')
            ->first();
        if (!$checkProjects && $checkIfUserTeamMember && $checkRole) {
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
            'role_id' => ['required',
                Rule::exists('roles', 'id')
                    ->where(function ($query) use ($projectId) {
                        $query->where('project_id', '=', $projectId);
                    })],
            'new_role_id' => ['required',
                Rule::exists('roles', 'id')
                    ->where(function ($query) use ($projectId) {
                        $query->where('project_id', '=', $projectId);
                    })],
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
                'role_id' => request()->segment(5),
                'user_id' => auth()->id(),
            ]);
        }
    }
}
