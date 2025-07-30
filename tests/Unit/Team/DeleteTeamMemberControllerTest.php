<?php

namespace Team;

use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use Tests\TestCase;

class DeleteTeamMemberControllerTest extends TestCase
{
    public function test_success_story()
    {
        $email = fake()->email;
        $identifyNumber = fake()->uuid();
        User::factory()->create([
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
        ]);
        $projectId = $project['project_id'];
        $teamMember = Team::factory()
            ->create([
                'project_id' => $projectId
            ]);
        $teamMemberId = $teamMember->id;
        $projectIdentify = $project['project_identify'];
        $response = $this->postJson("/api/projects/$projectIdentify/team/$teamMemberId/delete");
        $response->assertStatus(200);
    }

    public function test_not_found_team_member()
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
        Team::factory()
            ->create([
                'project_id' => $projectId
            ]);
        $projectIdentify = $project['project_identify'];
        $response = $this->postJson("/api/projects/$projectIdentify/team/{59}/delete");
        $response->assertStatus(403);
    }

    public function test_user_is_not_TeamMember_or_notAdmin()
    {
        $email = fake()->email;
        $identifyNumber = fake()->uuid();
        User::factory()->create([
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
        ]);

         $projectId = $project['project_id'];

        Team::factory(1)
            ->create([
                'project_id' => $projectId
            ]);
            $projectIdentify = $project['project_identify'];
        $response = $this->postJson("/api/projects/$projectIdentify/team/{5}/delete");
        $response->assertStatus(403);
    }
}
