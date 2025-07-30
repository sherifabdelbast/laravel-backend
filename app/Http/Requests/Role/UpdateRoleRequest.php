<?php

namespace App\Http\Requests\Role;

use App\Models\Clipboard;
use App\Models\Project;
use App\Models\Role;
use App\Models\Team;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRoleRequest extends FormRequest
{
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
     * Determine if the user is authorized to make this request.
     */
    public function rules(): array
    {
        $projectId = $this->input(['project_id']);
        return [
            'name' => [Rule::unique('roles', 'key')
                ->where(function ($query) use ($projectId) {
                    $query->where('project_id', '=', $projectId)
                        ->whereNull('deleted_at');
                })],
            'role_id' => ['required',
                Rule::exists('roles', 'id')
                    ->where(function ($query) use ($projectId) {
                        $query->where('project_id', '=', $projectId)
                            ->whereNull('deleted_at');
                    })],
            'permissions' => ['array'],
            'permissions.*' => ['string',
                Rule::exists('permissions', 'name')],
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

    public function messages()
    {
        return [
            'name.unique' => 'The name already exists.'
        ];
    }
}
