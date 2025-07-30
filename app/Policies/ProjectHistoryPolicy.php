<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\ProjectHistory;
use App\Models\Role;
use App\Models\Team;
use App\Models\User;

class ProjectHistoryPolicy
{
    public function view(User $user, ProjectHistory $projectHistory, $projectId): bool
    {

        $userId = auth()->id();
        $team = Team::query()
            ->where('user_id', '=', $userId)
            ->where('project_id', '=', $projectId)
            ->first();

        $roles = Role::query()
            ->where('id', '=', $team->role_id)
            ->first();
        $checkPermissions = $roles->hasPermissionTo('show project history');
        if ($checkPermissions) {
            return true;
        }
        return false;
    }
}
