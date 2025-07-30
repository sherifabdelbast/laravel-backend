<?php

namespace App\Http\Requests\Issue;

use App\Models\Clipboard;
use App\Models\Issue;
use App\Models\Project;
use App\Models\Sprint;
use App\Models\Status;
use App\Models\Team;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MoveIssueByStatusRequest extends FormRequest
{
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
            'status_id' => ['required', 'integer',
                Rule::exists('statuses', 'id')->where(function ($query) use ($projectId) {
                    $query->where('project_id', '=', $projectId)
                        ->where('id', '=', $this->input('status_id'));
                })],
            'order_by_status' => ['required'],
            'issue_id' => ['required'],
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
                'issue_id' => request()->segment(6)
            ]);
        }
    }
}
