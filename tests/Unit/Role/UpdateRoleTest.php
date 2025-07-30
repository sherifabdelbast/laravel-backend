<?php

namespace Tests\Unit\Role;

use App\Models\Role;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class UpdateRoleTest extends TestCase
{
    public function test_success_story()
    {
        $user = User::factory()->create([
            'name' => implode(' ', fake()->words(2)),
            'email' => fake()->email,
            'identify_number' => fake()->uuid(),
            'email_verified_at' => now(),
            'password' => 'MyPassword123'
        ]);
        $this->postJson('/login', [
            'email' => $user->email,
            'password' => 'MyPassword123'
        ]);
        $name = fake()->name;
        $project = $this->postJson("api/projects/create", [
            'name' => $name,
            'key' => substr($name, 0, 2),
            'user_id' => $user->id
        ]);
        $role = Role::query()
            ->where('project_id', '=', $project['project_id'])
            ->first();
        $projectIdentify = $project['project_identify'];
        $roleId = $role->id + 1;
        $permissions =Permission::query()
            ->get()
            ->pluck('name')
            ->toArray();

        $permissions = array_slice($permissions, 3, 5);

        $response = $this->postJson("api/projects/$projectIdentify/role/$roleId/edit", [
            'name' => fake()->title,
            'permissions' => $permissions
        ]);
        $response->assertStatus(200);
    }

    public function test_update_owner()
    {
        $user = User::factory()->create([
            'name' => implode(' ', fake()->words(2)),
            'email' => fake()->email,
            'identify_number' => fake()->uuid(),
            'email_verified_at' => now(),
            'password' => 'MyPassword123'
        ]);
        $this->postJson('/login', [
            'email' => $user->email,
            'password' => 'MyPassword123'
        ]);
        $name = fake()->name;
        $project = $this->postJson("api/projects/create", [
            'name' => $name,
            'key' => substr($name, 0, 2),
            'user_id' => $user->id
        ]);
        $role = Role::query()
            ->where('project_id', '=', $project['project_id'])
            ->first();
        $projectIdentify = $project['project_identify'];
        $roleId = $role->id;
        $permissions =Permission::query()
            ->get()
            ->pluck('name')
            ->toArray();

        $permissions = array_slice($permissions, 3, 5);

        $response = $this->postJson("api/projects/$projectIdentify/role/$roleId/edit", [
            'name' => fake()->title,
            'permissions' => $permissions
        ]);
        $response->assertStatus(403);
    }
}
