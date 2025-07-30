<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class RevokePermissionFromAdmin extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = Role::query()
            ->where('key', '=', 'admin')
            ->get();

        foreach ($roles as $role) {
            $role->revokePermissionTo(['close project']);
        }

        $roleShowIssue = Role::query()
            ->get();

        foreach ($roleShowIssue as $role) {
            $role->revokePermissionTo(['show issue']);
        }
        Permission::query()
            ->where('name', '=', 'show issue')
            ->forceDelete();

    }
}
