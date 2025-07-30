<?php

namespace Tests\Unit\Role;

use App\Models\Role;
use App\Models\Team;
use App\Models\User;
use Tests\TestCase;

class DeleteRoleTest extends TestCase
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
        $project = $this->postJson("api/projects/create",
            [
                'name' => $name,
                'key' => substr($name, 0, 2),
                'user_id' => $user->id
            ]);

        $projectIdentify = $project['project_identify'];
        $role = Role::query()
            ->where('project_id', '=', $project['project_id'])
            ->first();

        $roleAdmin = $role->id + 1;
        Team::factory(2)->create([
            'user_id' =>User::query()->inRandomOrder()->value('id'),
            'role_id' => $roleAdmin,
            'project_id' => $project['project_id']
        ]);
        $response = $this->postJson("api/projects/$projectIdentify/role/$roleAdmin/delete", [
            'new_role_id' => $role->id + 2
        ]);

        $response->assertStatus(200);
    }

    public function test_delete_owner()
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
        $project = $this->postJson("api/projects/create",
            [
                'name' => $name,
                'key' => substr($name, 0, 2),
                'user_id' => $user->id
            ]);

        $projectIdentify = $project['project_identify'];
        $role = Role::query()
            ->where('project_id', '=', $project['project_id'])
            ->first();

        $roleAdmin = $role->id;
        Team::factory(2)->create([
            'user_id' =>User::query()->inRandomOrder()->value('id'),
            'role_id' => $roleAdmin,
            'project_id' => $project['project_id']
        ]);
        $response = $this->postJson("api/projects/$projectIdentify/role/$roleAdmin/delete", [
            'new_role_id' => $role->id + 2
        ]);
        dd($response);
        $response->assertStatus(200);
    }
}
