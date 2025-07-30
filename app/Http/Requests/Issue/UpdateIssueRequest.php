<?php

namespace App\Http\Requests\Issue;

use App\Models\Clipboard;
use App\Models\Issue;
use App\Models\Project;
use App\Models\Status;
use App\Models\Team;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateIssueRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $userId = auth()->id();
        $issueId = request()->segment(6);
        $projectId = $this->input('project_id');

        $checkIfUserTeamMember = Team::query()
            ->where('project_id', '=', $projectId)
            ->where('user_id', '=', $userId)
            ->accept()
            ->first();

        $checkIssue = Issue::query()
            ->where('id', '=', $issueId)
            ->where('project_id', '=', $projectId)
            ->first();

        $checkProjects = Clipboard::query()
            ->where('project_id', '=', $projectId)
            ->archive()
            ->exists();

        if (!$checkProjects && $checkIfUserTeamMember && $checkIssue) {
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
            'title' => ['string'],
            'type' => ['string'],
            'sprint_id' => ['nullable',
                'integer',
                Rule::exists('sprints', 'id')
                    ->where(function ($query) use ($projectId) {
                        $query->where('project_id', $projectId);
                    })],
            'description' => ['nullable', 'string'],
            'assign_to' => ['nullable',
                'integer',
                Rule::exists('teams', 'id')
                    ->where(function ($query) use ($projectId) {
                        $query->where('project_id', $projectId)
                            ->where('access', '=', 1);
                    })],

            'status_id' => ['integer',
                Rule::exists('statuses', 'id')
                    ->where(function ($query) use ($projectId) {
                        $query->where('project_id', $projectId);
                    })],
            'mentionList' => ['nullable', 'array'],
            'estimated_at' => ['array'],
            'issue_id' => ['required'],
            'project_id' => ['required'],
            'user_id' => ['required'],
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
                'issue_id' => request()->segment(6)
            ]);
        }
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Title is required.',
            'title.min' => 'Issue title must be more than 2 characters.',
            'status_id.exists' => 'The selected status is invalid for the given project.',
        ];

    }
}
