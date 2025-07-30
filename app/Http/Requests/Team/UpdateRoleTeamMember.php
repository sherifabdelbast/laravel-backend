<?php

namespace App\Http\Requests\Team;

use App\Models\Clipboard;
use App\Models\Project;
use App\Models\Team;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRoleTeamMember extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $teamMemberId = request()->segment(5);
        $userId = auth()->id();

        $projectId = $this->input('project_id');

        $checkProjectOwner = Project::query()
            ->where('id', '=', $projectId)
            ->where('user_id', '=', $userId)
            ->first();

        $checkIfTeamMemberExists = Team::query()
            ->where('project_id', '=', $projectId)
            ->where('id', '=', $teamMemberId)
            ->withoutGlobalScopes()
            ->first();

        $checkProjects = Clipboard::query()
            ->where('project_id', '=', $projectId)
            ->archive()
            ->exists();

        if (!$checkProjects && $checkProjectOwner && $checkIfTeamMemberExists) {
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
        $projectId = $this->input('project_id');
        return [
            'role_id' => ['required',
                'integer',
                Rule::exists('roles', 'id')
                    ->where(function ($query) use ($projectId) {
                        $query->where('project_id', $projectId);
                    })],
            'teamMember_id' => ['required','integer'],
            'project_id' => ['required'],
            'user_id' => ['required'],
            'project_identify'=> ['required']
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
                'teamMember_id' => request()->segment(5),
                'user_id' => auth()->id(),
                'project_identify'=>request()->segment(3)
            ]);
        }
    }
}
