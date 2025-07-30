<?php

namespace App\Repositories;

use App\Models\Clipboard;
use App\Models\Issue;
use App\Models\Project;
use App\Models\ProjectHistory;
use App\Models\Role;
use App\Models\Sprint;
use App\Models\Status;
use App\Models\Team;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;

class ProjectRepository
{
    public function getProjectById($projectId)
    {
        return Project::query()
            ->where('id', '=', $projectId)
            ->first();
    }

    public function getProjectByIdentify($projectIdentify)
    {
        return Project::query()
            ->where('project_identify', '=', $projectIdentify)
            ->first();
    }

    public function getAllArchiveProjectToThisUser($userId)
    {
        return Clipboard::query()
            ->where('user_id', '=', $userId)
            ->archive()
            ->with('project.teamMembers.user')
            ->get()
            ->pluck('project')
            ->values()
            ->filter();
    }

    public function getDetailsToProject($project)
    {
        $sprint = Sprint::query()
            ->where('project_id', '=', $project->id)
            ->withoutGlobalScopes()
            ->get();
        $project->all_sprints = $sprint->count();

        $openSprint = $sprint->where('is_open', '=', 1);
        $project->open_sprints = $openSprint->count();

        $issues = Issue::query()
            ->where('project_id', '=', $project->id)
            ->withoutGlobalScopes()
            ->get();
        $project->all_issues = $issues->count();
        $bulkOpenSprint = $openSprint
            ->pluck('id')
            ->toArray();
        $openIssues = $issues->whereIn('sprint_id', $bulkOpenSprint);
        $project->open_issues = $openIssues->count();
        return $project;
    }

    public function getAllFavoriteProjectToThisUser($userId, $bulkArchiveProject)
    {
        return Clipboard::query()
            ->where('user_id', '=', $userId)
            ->where('favorite', '=', 1)
            ->whereNotIn('project_id', $bulkArchiveProject)
            ->with('project.teamMembers.user')
            ->get()
            ->pluck('project')
            ->values()
            ->filter();
    }

    public function getAllProjectsCreated($userId, $bulkArchiveProject)
    {
        return Project::query()
            ->where('user_id', '=', $userId)
            ->whereNotIn('id', $bulkArchiveProject)
            ->with(['teamMembers', 'teamMembers.user'])
            ->get();
    }

    public function getAllInvitedProjects($userId, $bulkProjects, $bulkArchiveProject)
    {
        return Team::query()
            ->where('user_id', '=', $userId)
            ->whereNotIn('project_id', array_merge($bulkProjects, $bulkArchiveProject))
            ->where(function ($query) {
                $query->where('access', '=', 1)
                    ->orWhereNull('access');
            })
            ->with('project.teamMembers.user')
            ->get()
            ->pluck('project')
            ->values()
            ->filter();
    }

    public function checkThisUserHasAnyProject($userId)
    {
        return Project::query()
            ->where('user_id', '=', $userId)
            ->first();
    }

    public function createProject($data)
    {
        $uploadIcon = null;
        $descriptionDefault = null;

        if (isset($data['icon'])) {
            $projectIcon = $data['icon'];
            $uploadIcon = Str::random(10) . '-'
                . $projectIcon->hashName();
            Storage::disk('public')->put('upload/projects_icon/' . $uploadIcon, file_get_contents($projectIcon));
        }

        if (isset($data['description'])) {
            $descriptionDefault = $data['description'];
        }

        DB::beginTransaction();
        try {
            do {
                $projectIdentify = Str::uuid();
                $project = Project::ProjectIdentify($projectIdentify)
                    ->exists();
            } while ($project);

            $project = Project::query()
                ->create([
                    'project_identify' => $projectIdentify,
                    'name' => $data['name'],
                    'description' => $descriptionDefault,
                    'key' => $data['key'],
                    'icon' => $uploadIcon,
                    'user_id' => $data['user_id']
                ]);
            $this->requirementsCreatedByDefaultWhenCreatingProject($project);
            $this->storeCreateProjectInProjectHistory($project->id, $data['user_id']);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
        return $project;
    }

    public function storeCreateProjectInProjectHistory($projectId, $userId)
    {
        return ProjectHistory::query()
            ->create([
                'status' => 'Create project',
                'type' => 'project',
                'action' => 'create',
                'project_id' => $projectId,
                'user_id' => $userId,
            ]);
    }

    public function requirementsCreatedByDefaultWhenCreatingProject($project): void
    {
        Sprint::query()
            ->create([
                'name' => 'sprint 1',
                'status' => 'IDLE',
                'start_date' => null,
                'project_id' => $project->id,
                'user_id' => $project->user_id
            ]);

        $statusValues = ['TO DO', 'IN PROGRESS', 'DONE'];
        $order = 1;
        foreach ($statusValues as $statusValue) {
            Status::query()
                ->create([
                    'name' => $statusValue,
                    'order' => $order,
                    'type' => $statusValue,
                    'project_id' => $project->id,
                    'user_id' => $project->user_id,
                ]);
            $order++;
        }

        $this->handleRoleForProject($project);

        $this->lastProjectOpenDefault($project->id, $project->user_id);
    }

    public function handleRoleForProject($project)
    {
        $roleOwner = Role::create([
            'name' => $project->key . '_' . $project->user_id . '_' . 'owner',
            'key' => 'owner',
            'project_id' => $project->id,
        ]);
        $roleAdmin = Role::create([
            'name' => $project->key . '_' . $project->user_id . '_' . 'admin',
            'key' => 'admin',
            'project_id' => $project->id,
        ]);

        $roleMember = Role::create([
            'name' => $project->key . '_' . $project->user_id . '_' . 'member',
            'key' => 'member',
            'project_id' => $project->id,
        ]);

        $roleViewer = Role::create([
            'name' => $project->key . '_' . $project->user_id . '_' . 'viewer',
            'key' => 'viewer',
            'project_id' => $project->id,
        ]);

        $permissions = Permission::query()
            ->get();

        $roleOwner->syncPermissions($permissions);
        $roleAdmin->syncPermissions($permissions);
        $roleAdmin->revokePermissionTo(['close project']);
        $roleMember->givePermissionTo([
            'create issue',
            'edit issue',
            'delete issue',
            'move issue backlog',
            'move issue board',
            'show issue history',
            'create comment',
            'edit comment',
            'delete comment',
        ]);
        $roleViewer->givePermissionTo([
            'create comment',
            'edit comment',
            'delete comment',
        ]);

        $team = Team::query()
            ->create([
                'access' => true,
                'invite_status' => 'accept',
                'role_id' => $roleOwner->id,
                'project_id' => $project->id,
                'user_id' => $project->user_id
            ]);
        $team->assignRole($roleOwner);
    }

    public function checkProjectExists($projectId, $userId)
    {
        $project = Project::query()
            ->where('id', '=', $projectId)
            ->with(['teamMembers', 'teamMembers.role', 'teamMembers.user'])
            ->first();

        if ($project) {
            $projectArchive = $this->checkIfProjectArchive($projectId, $userId);
            $project->is_archive = $projectArchive ? true : false;
        }
        return $project;
    }

    public function lastProjectOpenDefault($projectId, $userId)
    {
        $deleteDefaultProject = Clipboard::query()
            ->where('user_id', '=', $userId)
            ->where('default', '=', 1)
            ->first();

        $deleteDefaultProject?->delete();
        return Clipboard::query()
            ->create([
                'project_id' => $projectId,
                'user_id' => $userId,
                'default' => 1
            ]);
    }

    public function checkIfProjectArchive($projectId, $userId)
    {
        return Clipboard::query()
            ->where('project_id', '=', $projectId)
            ->where('user_id', '=', $userId)
            ->where('archive', '=', 1)
            ->first();
    }

    public function checkIfProjectInFavorites($data)
    {
        return Clipboard::query()
            ->where('project_id', '=', $data['project_id'])
            ->where('user_id', '=', $data['user_id'])
            ->where('favorite', '=', 1)
            ->first();
    }

    public function storeProjectInFavorites($data)
    {
        return Clipboard::query()->create([
            'project_id' => $data['project_id'],
            'user_id' => $data['user_id'],
            'favorite' => 1
        ]);
    }

    public function checkIfProjectInArchive($data)
    {
        return Clipboard::query()
            ->where('project_id', '=', $data['project_id'])
            ->where('user_id', '=', $data['user_id'])
            ->where('archive', '=', 1)
            ->first();
    }

    public function storeProjectInArchive($data)
    {
        Clipboard::query()
            ->where('project_id', '=', $data['project_id'])
            ->where('user_id', '=', $data['user_id'])
            ->delete();

        return Clipboard::query()->create([
            'project_id' => $data['project_id'],
            'user_id' => $data['user_id'],
            'archive' => 1
        ]);
    }

    public function updateProject($data)
    {
        $project = Project::query()->find($data['project_id']);
        DB::beginTransaction();
        try {

            if (isset($data['icon'])) {
                $projectIcon = $data['icon'];
                $uploadIcon = Str::random(10) . '-'
                    . $projectIcon->hashName();
                Storage::disk('public')->put('upload/projects_icon/' . $uploadIcon, file_get_contents($projectIcon));
                $project->update([
                    'icon' => $uploadIcon
                ]);
            }

            if ($data['name'] != $project->name) {
                $this->storeUpdateNameProjectInProjectHistory($data, $project->name);
            }
            if ($data['description'] != $project->description) {
                $this->storeUpdateDescriptionProjectInProjectHistory($data, $project->description);
            }

            $project->update([
                'name' => $data['name'] ?? $project->name,
                'description' => $data['description'],
            ]);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
        return $project->fresh();
    }

    public function storeUpdateNameProjectInProjectHistory($data, $oldData)
    {
        return ProjectHistory::query()
            ->create([
                'type' => 'name',
                'status' => 'Update project name',
                'action' => 'edit',
                'old_data' => $oldData,
                'new_data' => $data['name'],
                'user_id' => $data['user_id'],
                'project_id' => $data['project_id'],
            ]);
    }

    public function storeUpdateDescriptionProjectInProjectHistory($data, $oldData)
    {
        return ProjectHistory::query()
            ->create([
                'type' => 'description',
                'status' => 'Update project description',
                'action' => 'edit',
                'old_data' => $oldData,
                'new_data' => $data['description'],
                'user_id' => $data['user_id'],
                'project_id' => $data['project_id'],
            ]);
    }

    public function getProjectHistory($data)
    {
        return ProjectHistory::query()
            ->where('project_id', '=', $data['project_id'])
            ->with(['user', 'userWhoReceivedAction', 'statusWhichReceivedAction',
                'project', 'issue.teamMember.user', 'issue.status', 'sprintWhichReceivedAction',
                'issue', 'labelWhichReceivedAction', 'role'])
            ->latest('id')
            ->get();
    }

    public function openSprintForAllProjects($projectId)
    {
        return Sprint::query()
            ->where('project_id', '=', $projectId)
            ->where('is_open', '=', 1)
            ->get()
            ->pluck('id')
            ->toArray();
    }

    public function getActiveIssues($projectId)
    {
        $sprintsId = $this->openSprintForAllProjects($projectId);
        return Issue::query()
            ->whereIn('sprint_id', $sprintsId)
            ->get();
    }

    public function keyOfProjects($userId)
    {
        return Project::query()
            ->where('user_id', '=', $userId)
            ->pluck('key')
            ->toArray();
    }
}
