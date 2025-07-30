<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\Sprint;
use App\Models\Team;
use App\Models\User;

class SprintPolicy
{
    public function create(User $user, Sprint $sprint, $projectId): bool
    {
        $userId = auth()->id();
        $team = Team::query()
            ->where('user_id', '=', $userId)
            ->where('project_id', '=', $projectId)
            ->first();

        $roles = Role::query()
            ->where('id', '=', $team->role_id)
            ->first();

        $checkPermissions = $roles->hasPermissionTo('create sprint');
        if ($checkPermissions) {
            return true;
        }
        return false;
    }

    public function update(User $user, Sprint $sprint, $projectId): bool
    {
        $userId = auth()->id();
        $team = Team::query()
            ->where('user_id', '=', $userId)
            ->where('project_id', '=', $projectId)
            ->first();

        $roles = Role::query()
            ->where('id', '=', $team->role_id)
            ->first();
        $checkPermissions = $roles->hasPermissionTo('edit sprint');
        if ($checkPermissions) {
            return true;
        }
        return false;
    }

    public function destroy(User $user, Sprint $sprint, $projectId): bool
    {
        $userId = auth()->id();
        $team = Team::query()
            ->where('user_id', '=', $userId)
            ->where('project_id', '=', $projectId)
            ->first();

        $roles = Role::query()
            ->where('id', '=', $team->role_id)
            ->first();
        $checkPermissions = $roles->hasPermissionTo('delete sprint');
        if ($checkPermissions) {
            return true;
        }
        return false;
    }

    public function start(User $user, Sprint $sprint, $projectId): bool
    {
        $userId = auth()->id();
        $team = Team::query()
            ->where('user_id', '=', $userId)
            ->where('project_id', '=', $projectId)
            ->first();

        $roles = Role::query()
            ->where('id', '=', $team->role_id)
            ->first();
        $checkPermissions = $roles->hasPermissionTo('start sprint');
        if ($checkPermissions) {
            return true;
        }
        return false;
    }

    public function complete(User $user , Sprint $sprint, $projectId): bool
    {
        $userId = auth()->id();
        $team = Team::query()
            ->where('user_id', '=', $userId)
            ->where('project_id', '=', $projectId)
            ->first();

        $roles = Role::query()
            ->where('id', '=', $team->role_id)
            ->first();
        $checkPermissions = $roles->hasPermissionTo('complete sprint');
        if ($checkPermissions) {
            return true;
        }
        return false;
    }
}
