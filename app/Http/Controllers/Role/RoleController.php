<?php

namespace App\Http\Controllers\Role;

use App\Http\Controllers\Controller;
use App\Http\Requests\Role\DeleteRoleRequest;
use App\Http\Requests\Role\ShowRoleRequest;
use App\Http\Requests\Role\StoreRoleRequest;
use App\Http\Requests\Role\UpdateRoleRequest;
use App\Models\Role;
use App\Repositories\NotificationRepository;
use App\Repositories\ProjectRepository;
use App\Repositories\RoleRepository;
use App\Repositories\TeamRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
    public function index(ShowRoleRequest $request,
                          RoleRepository  $roleRepository,
                          Role            $role)
    {
        $data = $request->validated();
        $this->authorize('view', [$role, $data['project_id']]);

        $getAllRoles = $roleRepository->getAllRole($data['project_id']);
        return response([
            'message' => 'List of all role',
            'code' => 200,
            'roles' => $getAllRoles
        ], 200);
    }

    public function store(StoreRoleRequest  $request,
                          RoleRepository    $roleRepository,
                          ProjectRepository $projectRepository,
                          Role              $role)
    {
        $data = $request->validated();
        $this->authorize('create', [$role, $data['project_id']]);
        DB::beginTransaction();
        try {
            $project = $projectRepository->getProjectById($data['project_id']);
            $newRole = $roleRepository->createNewRole($data, $project);
            $roleRepository->storeCreateRoleInProjectHistory($data, $newRole->id);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
        return response([
            'message' => 'Role create successfully',
            'code' => 201,
        ], 201);
    }

    public function update(UpdateRoleRequest      $request,
                           RoleRepository         $roleRepository,
                           ProjectRepository      $projectRepository,
                           UserRepository         $userRepository,
                           NotificationRepository $notificationRepository,
                           Role                   $role)
    {
        $data = $request->validated();
        $this->authorize('update', [$role, $data['project_id']]);

        DB::beginTransaction();
        try {
            $role = $roleRepository->getRoleById($data['role_id']);
            $project = $projectRepository->getProjectById($data['project_id']);

            if (isset($data['name'])) {
                $roleRepository->storeUpdateNameRoleInProjectHistory($data, $role);
                $roleRepository->updateRoleName($data, $role, $project);
            }

            if (isset($data['permissions'])) {
                $permissions = $data['permissions'];
                // assign new permissions to role
                foreach ($permissions as $permission) {
                    $checkIfRoleHasPermission = $role->hasPermissionTo($permission);
                    if (!$checkIfRoleHasPermission) {
                        $data['permission'] = $permission;
                        $roleRepository->updateRolePermission($data, $role);
                        $roleRepository->storeUpdatePermissionsRoleInProjectHistory($data, $role->id);
                    }
                }
                // revoke permissions from role
                $revokedPermissions = $roleRepository->getPermissionsHasBeenRevoke($permissions, $role);
                foreach ($revokedPermissions as $permission) {
                    $roleRepository->deleteRolePermission($permission, $role);
                }
                $roleRepository->storeUpdatePermissionsRoleInProjectHistory($data, $role->id);
            }
            $type = 'role';
            $title = 'updated role';
            $userWhoDid = $userRepository->getUserById($data['user_id']);
            $content = $userWhoDid->name . ' updated role ' . $role->key;
            $project = $projectRepository->getProjectById($data['project_id']);
            $user = $userRepository->getUserById($project->user_id);
            if ($data['user_id'] != $user->id && $user->player_ids != null) {
                $notificationRepository
                    ->storeRoleNotification($data, $user, $type, $title, 'updated role', $content, $role->id);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
        return response([
            'message' => 'Update Role successfully',
            'code' => 200,
        ], 200);
    }

    public function destroy(DeleteRoleRequest $request,
                            RoleRepository    $roleRepository,
                            TeamRepository    $teamRepository,
                            Role              $role)
    {
        $data = $request->validated();
        $this->authorize('delete', [$role, $data['project_id']]);
        DB::beginTransaction();
        try {
            $teamMember = $teamRepository->getAllTeamMemberSameRole($data);
            $role = $roleRepository->getRoleById($data['new_role_id']);
            foreach ($teamMember as $member) {
                $teamRepository->updateRoleToMemberWhenDeleteRole($data, $member, $role);
            }
            $role = $roleRepository->getRoleById($data['role_id']);
            $roleRepository->storeDeleteRoleInProjectHistory($data, $role->id);
            $roleRepository->deleteRole($role);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
        return response([
            'message' => 'The role has been deleted successfully',
            'code' => 200,
        ], 200);
    }
}
