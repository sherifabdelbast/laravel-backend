<?php

namespace Tests\Unit\Issue;

use App\Models\Project;
use App\Models\User;
use Tests\TestCase;

class CreateIssueTest extends TestCase
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
        $response = $this->postJson("api/projects/$projectIdentify/issues/create", [
            'title' => fake()->title,
            'sprint_id' => null,
            'type' => "bug"
        ]);

        $response->assertStatus(201);
    }

    public function test_data_is_not_valid()
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
        $response = $this->postJson("api/projects/$projectIdentify/issues/create", [
            'title' => ""
        ]);

        $response->assertStatus(422);
    }

}
