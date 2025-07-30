<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class ModifyingOldDataInRoleTable extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $projects = Project::all();

        foreach ($projects as $project) {

            $roleOwner = Role::create([
                'name' => $project->key . '_' . $project->user_id . '_' . 'owner',
                'key' => 'owner',
                'project_id' => $project->id,
            ]);
            $roleAdmin = Role::create([
                'name' => $project->key . '_' . $project->user_id . '_' . 'admin',
                'key' => 'admin',
                'project_id' => $project->id,
            ]);

            $roleMember = Role::create([
                'name' => $project->key . '_' . $project->user_id . '_' . 'member',
                'key' => 'member',
                'project_id' => $project->id,
            ]);

            $roleViewer = Role::create([
                'name' => $project->key . '_' . $project->user_id . '_' . 'viewer',
                'key' => 'viewer',
                'project_id' => $project->id,
            ]);

            $permissions = Permission::query()
                ->get();

            $roleOwner->syncPermissions($permissions);
            $roleAdmin->syncPermissions($permissions);
            $roleAdmin->revokePermissionTo(['close project']);
            $roleMember->givePermissionTo([
                'create issue',
                'edit issue',
                'delete issue',
                'move issue backlog',
                'move issue board',
                'show issue history',
                'create comment',
                'edit comment',
                'delete comment',
            ]);
            $roleViewer->givePermissionTo([
                'create comment',
                'edit comment',
                'delete comment',
            ]);
        }
    }
}
