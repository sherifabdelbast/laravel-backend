<?php

namespace App\Repositories;

use App\Models\ProjectHistory;
use App\Models\Role;
use App\Models\Team;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;

class RoleRepository
{
    public function getAllRole($projectId)
    {

        return Role::query()
            ->select(['id', 'name', 'key'])
            ->where('project_id', '=', $projectId)
            ->with(['permissions:name'])
            ->get()
            ->map(function ($role) use ($projectId) {
                $data['role_id'] = $role->id;
                $data['project_id'] = $projectId;
                $countTeamMember = $this->getAllTeamMemberSameRole($data)
                    ->count();
                return [
                    'id' => $role->id,
                    'name' => $role->name,
                    'key' => $role->key,
                    'countOfMembers' => $countTeamMember,
                    'permissions' => $role->permissions->pluck('name')->toArray(),
                ];
            });
    }

    public function getAllTeamMemberSameRole($data)
    {
        return Team::query()
            ->where('project_id', '=', $data['project_id'])
            ->where('role_id', '=', $data['role_id'])
            ->get();
    }

    public function getRoleById($roleId)
    {
        return Role::query()
            ->where('id', '=', $roleId)
            ->first();
    }

    public function getAllPermissions()
    {
        return Permission::query()
            ->get()
            ->pluck('name')
            ->toArray();
    }

    public function createNewRole($data, $project)
    {
        $random = Str::random(3);
        $role = Role::create([
            'name' => $project->key . '_' . $project->user_id . '_' . $data['name'] . '-' . $random,
            'key' => $data['name'],
            'project_id' => $project->id
        ]);
        $role->syncPermissions($data['permissions']);
        return $role;
    }

    public function storeCreateRoleInProjectHistory($data, $roleId)
    {
        ProjectHistory::query()
            ->create([
                'status' => 'Create the role',
                'type' => 'role',
                'action' => 'create',
                'role_id' => $roleId,
                'project_id' => $data['project_id'],
                'user_id' => $data['user_id'],
            ]);
    }

    public function updateRoleName($data, $role, $project)
    {
        $role->update([
            'name' => $project->key . '_' . $project->user_id . '_' . $data['name'],
            'key' => $data['name'],
        ]);
    }

    public function updateRolePermission($data, $role)
    {
        return $role->givePermissionTo($data['permission']);
    }

    public function deleteRolePermission($permission, $role)
    {
        return $role->revokePermissionTo($permission);
    }

    public function storeUpdateNameRoleInProjectHistory($data, $role)
    {
        ProjectHistory::query()
            ->create([
                'status' => 'Update the role name',
                'type' => 'role',
                'action' => 'update',
                'new_data' => $data['name'],
                'old_data' => $role->key,
                'role_id' => $role->id,
                'project_id' => $data['project_id'],
                'user_id' => $data['user_id'],
            ]);
    }

    public function storeUpdatePermissionsRoleInProjectHistory($data, $roleId)
    {
        ProjectHistory::query()
            ->create([
                'status' => 'Update the role permissions',
                'type' => 'role',
                'action' => 'update',
                'role_id' => $roleId,
                'project_id' => $data['project_id'],
                'user_id' => $data['user_id'],
            ]);
    }

    public function deleteRole($role)
    {
        $role->delete();
    }

    public function storeDeleteRoleInProjectHistory($data, $roleId)
    {
        ProjectHistory::query()
            ->create([
                'status' => 'Delete the role',
                'type' => 'role',
                'action' => 'Delete',
                'role_id' => $roleId,
                'project_id' => $data['project_id'],
                'user_id' => $data['user_id'],
            ]);
    }

    public function getPermissionsHasBeenRevoke($permissions, $role)
    {
        $oldPermissions = $role->permissions
            ->pluck('name')
            ->toArray();
        return array_diff($oldPermissions, $permissions);
    }
}
