<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\Status;
use App\Models\Team;
use App\Models\User;

class StatusPolicy
{
    public function create(User $user, Status $status, $projectId): bool
    {
        $userId = auth()->id();
        $team = Team::query()
            ->where('user_id', '=', $userId)
            ->where('project_id', '=', $projectId)
            ->first();

        $roles = Role::query()
            ->where('id', '=', $team->role_id)
            ->first();
        $checkPermissions = $roles->hasPermissionTo('create status');
        if ($checkPermissions) {
            return true;
        }
        return false;
    }


    public function update(User $user, Status $status, $projectId): bool
    {
        $userId = auth()->id();

        $team = Team::query()
            ->where('user_id', '=', $userId)
            ->where('project_id', '=', $projectId)
            ->first();

        $roles = Role::query()
            ->where('id', '=', $team->role_id)
            ->first();

        $checkPermissions = $roles->hasPermissionTo('edit status');
        if ($checkPermissions) {
            return true;
        }
        return false;
    }

    public function move(User $user, Status $status, $projectId): bool
    {
        $userId = auth()->id();
        $team = Team::query()
            ->where('user_id', '=', $userId)
            ->where('project_id', '=', $projectId)
            ->first();

        $roles = Role::query()
            ->where('id', '=', $team->role_id)
            ->first();
        $checkPermissions = $roles->hasPermissionTo('move status');
        if ($checkPermissions) {
            return true;
        }
        return false;
    }

    public function delete(User $user, Status $status, $projectId): bool
    {
        $userId = auth()->id();
        $team = Team::query()
            ->where('user_id', '=', $userId)
            ->where('project_id', '=', $projectId)
            ->first();
        $roles = Role::query()
            ->where('id', '=', $team->role_id)
            ->first();
        $checkPermissions = $roles->hasPermissionTo('delete status');
        if ($checkPermissions) {
            return true;
        }
        return false;
    }
}
