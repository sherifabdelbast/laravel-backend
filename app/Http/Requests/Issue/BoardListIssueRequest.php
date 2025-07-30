<?php

namespace App\Http\Requests\Issue;

use App\Models\Clipboard;
use App\Models\Project;
use App\Models\Team;
use Illuminate\Foundation\Http\FormRequest;

class BoardListIssueRequest extends FormRequest
{
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


    public function rules(): array
    {
        return
            [
                'user_id' => ['required'],
                'project_id' => ['required'],
                'search' => ['string', 'nullable'],
                'assignee' => ['integer', 'nullable'],
                'status' => ['integer', 'nullable'],
                'sprint' => ['integer', 'nullable'],
                'label'=> ['string', 'nullable'],
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
                'search' => $this->query('search'),
                'assignee' => $this->query('assignee'),
                'status' => $this->query('status'),
                'sprint' => $this->query('sprint'),
            ]);
        }
    }
}
