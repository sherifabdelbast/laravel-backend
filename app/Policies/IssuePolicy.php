<?php

namespace App\Policies;

use App\Models\Issue;
use App\Models\Role;
use App\Models\Team;
use App\Models\User;

class IssuePolicy
{
    public function create(User $user, Issue $issue, $projectId): bool
    {
        $userId = auth()->id();
        $team = Team::query()
            ->where('user_id', '=', $userId)
            ->where('project_id', '=', $projectId)
            ->first();

        $roles = Role::query()
            ->where('id', '=', $team->role_id)
            ->first();
        $checkPermissions = $roles->hasPermissionTo('create issue');
        if ($checkPermissions) {
            return true;
        }
        return false;
    }

    public function update(User $user, Issue $issue, $projectId): bool
    {
        $userId = auth()->id();
        $team = Team::query()
            ->where('user_id', '=', $userId)
            ->where('project_id', '=', $projectId)
            ->first();

        $roles = Role::query()
            ->where('id', '=', $team->role_id)
            ->first();
        $checkPermissions = $roles->hasPermissionTo('edit issue');
        if ($checkPermissions) {
            return true;
        }
        return false;
    }

    public function delete(User $user, Issue $issue, $projectId): bool
    {
        $userId = auth()->id();
        $team = Team::query()
            ->where('user_id', '=', $userId)
            ->where('project_id', '=', $projectId)
            ->first();

        $roles = Role::query()
            ->where('id', '=', $team->role_id)
            ->first();
        $checkPermissions = $roles->hasPermissionTo('delete issue');
        if ($checkPermissions) {
            return true;
        }
        return false;
    }

    public function moveBacklog(User $user, Issue $issue, $projectId): bool
    {
        $userId = auth()->id();
        $team = Team::query()
            ->where('user_id', '=', $userId)
            ->where('project_id', '=', $projectId)
            ->first();

        $roles = Role::query()
            ->where('id', '=', $team->role_id)
            ->first();
        $checkPermissions = $roles->hasPermissionTo('move issue backlog');
        if ($checkPermissions) {
            return true;
        }
        return false;
    }

    public function moveBoard(User $user, Issue $issue, $projectId): bool
    {
        $userId = auth()->id();
        $team = Team::query()
            ->where('user_id', '=', $userId)
            ->where('project_id', '=', $projectId)
            ->first();

        $roles = Role::query()
            ->where('id', '=', $team->role_id)
            ->first();
        $checkPermissions = $roles->hasPermissionTo('move issue board');
        if ($checkPermissions) {
            return true;
        }
        return false;
    }
}
