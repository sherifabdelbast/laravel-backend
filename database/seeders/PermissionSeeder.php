<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            'edit project',
            'close project',
            'show project history',

            'create sprint',
            'edit sprint',
            'start sprint',
            'complete sprint',
            'delete sprint',

            'create status',
            'edit status',
            'move status',
            'delete status',

            'invite team',
            'edit team',
            'remove team',

            'create issue',
            'edit issue',
            'delete issue',
            'move issue backlog',
            'move issue board',
            'show issue history',

            'create comment',
            'edit comment',
            'delete comment',

            'show role',
            'create role',
            'edit role',
            'delete role',
        ];

        foreach ($permissions as $permission) {
            Permission::query()->create([
                'name' => $permission,
                'guard_name' => 'customer'
            ]);
        }
    }
}
