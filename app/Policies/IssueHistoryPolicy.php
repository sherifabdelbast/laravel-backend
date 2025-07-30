<?php

namespace App\Policies;

use App\Models\IssueHistory;
use App\Models\Role;
use App\Models\Team;
use App\Models\User;

class IssueHistoryPolicy
{
    public function view(User $user, IssueHistory $issueHistory, $projectId): bool
    {
        $userId = auth()->id();
        $team = Team::query()
            ->where('user_id', '=', $userId)
            ->where('project_id', '=', $projectId)
            ->first();

        $roles = Role::query()
            ->where('id', '=', $team->role_id)
            ->first();

        $checkPermissions = $roles->hasPermissionTo('show issue history');
        if ($checkPermissions) {
            return true;
        }
        return false;
    }

}
