<?php

namespace App\Http\Controllers\Role;

use App\Http\Controllers\Controller;
use App\Http\Requests\Role\DeletePermissionRequest;
use App\Http\Requests\Role\ShowRoleRequest;
use App\Models\Role;
use App\Repositories\RoleRepository;
use App\Repositories\TeamRepository;
use Illuminate\Support\Facades\DB;

class PermissionController extends Controller
{
    public function index(ShowRoleRequest $request,
                          RoleRepository  $roleRepository)
    {
        $request->validated();
        $getAllPermissions = $roleRepository->getAllPermissions();
        return response([
            'message' => 'List of all permissions',
            'code' => 200,
            'permissions' => $getAllPermissions
        ], 200);
    }

    public function show(ShowRoleRequest $request,
                         RoleRepository  $roleRepository,
                         TeamRepository  $teamRepository)
    {
        $data = $request->validated();
        $teamMember = $teamRepository->getTeamMemberByUserId($data);

        $getAllPermissionsToMember = $roleRepository->getAllRole($data['project_id'])
            ->where('id', '=', $teamMember->role_id)
            ->first();

        return response([
            'message' => 'List of permissions for the team member',
            'code' => 200,
            'role' => $getAllPermissionsToMember
        ], 200);
    }
}

