<?php

namespace App\Http\Requests\Role;

use App\Models\Clipboard;
use App\Models\Project;
use App\Models\Team;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePermissionRequest extends FormRequest
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
     * Determine if the user is authorized to make this request.
     */
    public function rules(): array
    {
        return [
            'permission' => ['string', Rule::exists('permissions', 'name')],
            'role_id' => ['required'],
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
