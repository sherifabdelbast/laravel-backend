<?php

namespace Tests\Unit\Project;

use App\Models\Project;
use App\Models\User;
use Tests\TestCase;

class UpdateProjectTest extends TestCase
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

        $projectIdentify = $project['project_identify'];
        $response = $this->postJson("api/projects/$projectIdentify/edit", [
            'name' => 'mohammed',
            'description' => 'test'
        ]);
        $response->assertStatus(200);
    }

    public function test_forbidden()
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
        $project = Project::factory()->create([
            'name' => fake()->name,
            'user_id' => User::query()->inRandomOrder()->value('id')
        ]);
        $projectIdentify = $project['project_identify'];
        $response = $this->postJson("api/projects/$projectIdentify/edit", [
            'name' => 'mohammed',
        ]);
        $response->assertStatus(403);
    }

    public function test_unValid_data()
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
        $projectIdentify = $project['project_identify'];
        $response = $this->postJson("api/projects/$projectIdentify/edit", [
            'name' => null,
        ]);
        $response->assertStatus(422);
    }
}
