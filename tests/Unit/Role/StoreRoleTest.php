<?php

namespace Tests\Unit\Role;

use App\Models\User;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class StoreRoleTest extends TestCase
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
        $permissions =Permission::query()
        ->get()
        ->pluck('name')
        ->toArray();

        $permissions = array_slice($permissions, 3, 5);


        $projectIdentify = $project['project_identify'];
        $response = $this->postJson("api/projects/$projectIdentify/role/create", [
            'name' => fake()->title,
            'permissions' => $permissions
        ]);
        $response->assertStatus(201);
    }
}
