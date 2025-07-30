<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\Role;
use App\Models\Team;
use App\Models\User;

class ProjectPolicy
{
    public function update(User $user, Project $project, $projectId): bool
    {
        $userId = auth()->id();
        $team = Team::query()
            ->where('user_id', '=', $userId)
            ->where('project_id', '=', $projectId)
            ->first();

        $roles = Role::query()
            ->where('id', '=', $team->role_id)
            ->first();
        $checkPermissions = $roles->hasPermissionTo('edit project');
        if ($checkPermissions) {
            return true;
        }
        return false;
    }

    public function delete(User $user, Project $project, $projectId): bool
    {
        $userId = auth()->id();
        $team = Team::query()
            ->where('user_id', '=', $userId)
            ->where('project_id', '=', $projectId)
            ->first();
        $roles = Role::query()
            ->where('id', '=', $team->role_id)
            ->first();
        $checkPermissions = $roles->hasPermissionTo('close project');
        if ($checkPermissions) {
            return true;
        }
        return false;
    }

}
