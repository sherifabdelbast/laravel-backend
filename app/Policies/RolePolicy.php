<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\Team;
use App\Models\User;

class RolePolicy
{
    public function view(User $user, Role $role, $projectId): bool
    {
        $userId = auth()->id();
        $team = Team::query()
            ->where('user_id', '=', $userId)
            ->where('project_id', '=', $projectId)
            ->first();

        $roles = Role::query()
            ->where('id', '=', $team->role_id)
            ->first();

        $checkPermissions = $roles->hasPermissionTo('show role');
        if ($checkPermissions) {
            return true;
        }
        return false;
    }

    public function create(User $user, Role $role, $projectId): bool
    {
        $userId = auth()->id();
        $team = Team::query()
            ->where('user_id', '=', $userId)
            ->where('project_id', '=', $projectId)
            ->first();

        $roles = Role::query()
            ->where('id', '=', $team->role_id)
            ->first();

        $checkPermissions = $roles->hasPermissionTo('create role');
        if ($checkPermissions) {
            return true;
        }
        return false;
    }

    public function update(User $user, Role $role, $projectId): bool
    {
        $userId = auth()->id();
        $team = Team::query()
            ->where('user_id', '=', $userId)
            ->where('project_id', '=', $projectId)
            ->first();

        $roles = Role::query()
            ->where('id', '=', $team->role_id)
            ->first();

        $checkPermissions = $roles->hasPermissionTo('edit role');
        if ($checkPermissions) {
            return true;
        }
        return false;
    }

    public function delete(User $user, Role $role, $projectId): bool
    {
        $userId = auth()->id();
        $team = Team::query()
            ->where('user_id', '=', $userId)
            ->where('project_id', '=', $projectId)
            ->first();

        $roles = Role::query()
            ->where('id', '=', $team->role_id)
            ->first();

        $checkPermissions = $roles->hasPermissionTo('delete role');
        if ($checkPermissions) {
            return true;
        }
        return false;
    }
}
