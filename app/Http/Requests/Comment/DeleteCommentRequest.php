<?php

namespace App\Http\Requests\Comment;

use App\Models\Clipboard;
use App\Models\Comment;
use App\Models\Issue;
use App\Models\Project;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class DeleteCommentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $userId = auth()->id();
        $issueId = request()->segment(6);
        $commentId = request()->segment(8);
        $projectId = $this->input('project_id');


        $checkUser = Comment::query()
            ->where('id', '=', $commentId)
            ->where('user_id', '=', $userId)
            ->first();

        $checkCreator = Project::query()
            ->where('id', '=', $projectId)
            ->where('user_id', '=', $userId)
            ->first();

        $checkIssue = Issue::query()
            ->where('id', '=', $issueId)
            ->where('project_id', '=', $projectId)
            ->first();

        $checkProjects = Clipboard::query()
            ->where('project_id', '=', $projectId)
            ->archive()
            ->exists();

        if (!$checkProjects && ($checkUser || $checkCreator) && $checkIssue) {
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
            'project_id' => ['required'],
            'issue_id' => ['required'],
            'user_id' => ['required'],
            'comment_id' => ['required']
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
                'issue_id' => request()->segment(6),
                'comment_id' => request()->segment(8)
            ]);
        }
    }
}
