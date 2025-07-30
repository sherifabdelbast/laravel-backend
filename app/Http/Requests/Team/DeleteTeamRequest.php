<?php

namespace App\Http\Requests\Team;

use App\Models\Clipboard;
use App\Models\Project;
use App\Models\Team;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class DeleteTeamRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $teamMemberId = request()->segment(5);
        $userId = auth()->id();
        $projectIdentify = request()->segment(3);

        $project = $this->projectIdInitialization($projectIdentify);
        $projectId = $project->id;

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
        return [
            'teamMember_id' => 'required',
            'project_id' => 'required',
            'user_id' => 'required'
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
                'user_id' => auth()->id()
            ]);
        }
    }
}
