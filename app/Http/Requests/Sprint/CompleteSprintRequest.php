<?php

namespace App\Http\Requests\Sprint;

use App\Models\Clipboard;
use App\Models\Project;
use App\Models\Sprint;
use App\Models\Team;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CompleteSprintRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $userId = auth()->id();
        $sprintId = request()->segment(6);
        $projectId = $this->input('project_id');


        $checkIfUserTeamMember = Team::query()
            ->where('project_id', '=', $projectId)
            ->where('user_id', '=', $userId)
            ->accept()
            ->first();

        $checkSprint = Sprint::query()
            ->where('id', '=', $sprintId)
            ->where('project_id', '=', $projectId)
            ->where('is_open', '=', 1)
            ->first();

        $checkProjects = Clipboard::query()
            ->where('project_id', '=', $projectId)
            ->archive()
            ->exists();

        if (!$checkProjects && $checkIfUserTeamMember && $checkSprint) {
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
            'issue_sprint_id' => ['integer', 'nullable',
                Rule::exists('sprints', 'id')->where(function ($query) use ($projectId) {
                    $query->where('project_id', $projectId);
                })],
            'is_completed' => ['nullable'],
            'sprint_id' => ['required'],
            'user_id' => ['required'],
            'project_id' => ['required'],
            'project_identify'=>['required']
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
                'project_identify'=>request()->segment(3),
                'sprint_id' => request()->segment(6),
            ]);
        }
    }
}
