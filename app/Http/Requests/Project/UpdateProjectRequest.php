<?php

namespace App\Http\Requests\Project;

use App\Models\Clipboard;
use App\Models\Project;
use App\Models\Team;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectRequest extends FormRequest
{

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $userId = auth()->id();
        $projectId = $this->input('project_id');


        $checkProjectInvitation = Team::query()
            ->where('project_id', '=', $projectId)
            ->where('user_id', '=', $userId)
            ->accept()
            ->first();

        $checkProjects = Clipboard::query()
            ->where('project_id', '=', $projectId)
            ->archive()
            ->exists();

        if (!$checkProjects && $checkProjectInvitation) {
            return true;
        }
        return false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required','string'],
            'description' => ['nullable','string'],
            'icon' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
            'project_id' => ['required'],
            'user_id' => ['required']
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
                'project_identify' => request()->segment(3),
                'user_id' => auth()->id(),
            ]);
        }
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Project name is required.',
            'name.min' => 'Project name must be more than 3 characters.',
            'icon.mimes' => 'Invalid image format! Allowed extensions: .png, .jpg, .jpeg.',
            'icon.max' => 'The maximum allowed image size for uploads is 5MB.',
        ];
    }
}
