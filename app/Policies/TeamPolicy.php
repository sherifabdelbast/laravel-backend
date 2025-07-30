<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\Team;
use App\Models\User;

class TeamPolicy
{
    public function invite(User $user, Team $team, $projectId): bool
    {
        $userId = auth()->id();
        $teamMember = Team::query()
            ->where('user_id', '=', $userId)
            ->where('project_id', '=', $projectId)
            ->first();

        $roles = Role::query()
            ->where('id', '=', $teamMember->role_id)
            ->first();
        $checkPermissions = $roles->hasPermissionTo('invite team');
        if ($checkPermissions) {
            return true;
        }
        return false;
    }

    public function update(User $user, Team $team, $projectId): bool
    {
        $userId = auth()->id();
        $teamMember = Team::query()
            ->where('user_id', '=', $userId)
            ->where('project_id', '=', $projectId)
            ->first();

        $roles = Role::query()
            ->where('id', '=', $teamMember->role_id)
            ->first();
        $checkPermissions = $roles->hasPermissionTo('edit team');
        if ($checkPermissions) {
            return true;
        }
        return false;
    }

    public function delete(User $user, Team $team, $projectId): bool
    {
        $userId = auth()->id();
        $teamMember = Team::query()
            ->where('user_id', '=', $userId)
            ->where('project_id', '=', $projectId)
            ->first();

        $roles = Role::query()
            ->where('id', '=', $teamMember->role_id)
            ->first();
        $checkPermissions = $roles->hasPermissionTo('remove team');
        if ($checkPermissions) {
            return true;
        }
        return false;
    }
}
