<?php

namespace Tests\Unit\Team;

use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use Tests\TestCase;

class ListTeamControllerTest extends TestCase
{
    public function test_success_story()
    {
        $email = fake()->email;
        $identifyNumber = fake()->uuid();
        $user = User::factory()->create([
            'name' => implode(' ', fake()->words(2)),
            'email' => $email,
            'identify_number' => $identifyNumber,
            'email_verified_at' => now(),
            'password' => 'MyPassword123'
        ]);
        $this->postJson('/login', [
            'email' => $email,
            'password' => 'MyPassword123'
        ]);

        $name = fake()->name;
        $project = $this->postJson("api/projects/create", [
            'name' => $name,
            'key' => substr($name, 0, 2),
            'user_id' => $user->id
        ]);

        $projectId = $project['project_id'];
        Team::factory(3)
            ->create([
                'project_id' => $projectId,
            ]);
        $projectIdentify = $project['project_identify'];
        $response = $this->getJson("/api/projects/$projectIdentify/team");
        $response->assertStatus(200);
    }

    public function test_user_is_not_TeamMember()
    {
        $email = fake()->email;
        $identifyNumber = fake()->uuid();
        User::factory()->create([
            'name' => implode('', fake()->words(2)),
            'email' => $email,
            'identify_number' => $identifyNumber,
            'email_verified_at' => now(),
            'password' => 'MyPassword123'
        ]);
        $this->postJson('/login', [
            'email' => $email,
            'password' => 'MyPassword123'
        ]);

        $projectIdentify = Project::query()->inRandomOrder()->value('project_identify');
        $response = $this->get("/api/projects/$projectIdentify/team");
        $response->assertStatus(403);
    }
}
