<?php

namespace Tests\Unit\Team;

use App\Models\Invitation;
use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use Tests\TestCase;

class AcceptInvitationTest extends TestCase
{
    public function test_success_story_accept()
    {
        $email = fake()->email;
        $identifyNumber = fake()->uuid();
        $userAdmin = User::factory()->create([
            'name' => implode(' ', fake()->words(2)),
            'email' => fake()->email,
            'identify_number' => fake()->uuid(),
            'email_verified_at' => now(),
            'password' => 'MyPassword123'
        ]);
        $this->postJson('/login', [
            'email' => $userAdmin->email,
            'password' => 'MyPassword123'
        ]);

        $project = Project::factory()->create([
            'user_id' => $userAdmin->id,
            'name' => fake()->name,
        ]);
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

        $projectId = $project->id;
        $teamMember = Team::factory()
            ->create([
                'project_id' => $projectId,
                'access' => 0,
                'role_id' => 1,
                'user_id' => $user->id
            ]);
        $inviteIdentify = fake()->uuid();
        Invitation::factory()
            ->create([
                'invite_identify' => $inviteIdentify,
                'member_id' => $teamMember->id,
                'role_id' => 1,
                'project_id' => $projectId,
                'user_id' => $userAdmin->id,
            ]);
        $response = $this->post("/api/invitation/accept",
            [
                'invite_identify' => $inviteIdentify,
                'accept' => 0
            ]);
        $response->assertStatus(200);
    }

    public function test_success_story_reject()
    {
        $email = fake()->email;
        $identifyNumber = fake()->uuid();
        $userAdmin = User::factory()->create([
            'name' => implode(' ', fake()->words(2)),
            'email' => fake()->email,
            'identify_number' => fake()->uuid(),
            'email_verified_at' => now(),
            'password' => 'MyPassword123'
        ]);
        $this->postJson('/login', [
            'email' => $userAdmin->email,
            'password' => 'MyPassword123'
        ]);

        $project = Project::factory()->create([
            'user_id' => $userAdmin->id,
            'name' => fake()->name,
        ]);
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

        $projectId = $project->id;
        $teamMember = Team::factory()
            ->create([
                'project_id' => $projectId,
                'access' => 0,
                'role_id' => 1,
                'user_id' => $user->id
            ]);
        $inviteIdentify = fake()->uuid();
        Invitation::factory()
            ->create([
                'invite_identify' => $inviteIdentify,
                'member_id' => $teamMember->id,
                'role_id' => 1,
                'project_id' => $projectId,
                'user_id' => $userAdmin->id,
            ]);
        $response = $this->postJson("/api/invitation/accept",
            [
                'invite_identify' => $inviteIdentify,
                'accept' => 0
            ]);
        $response->assertStatus(200);
    }


    public function test_not_found_the_invitation()
    {
        $email = fake()->email;
        $identifyNumber = fake()->uuid();
        $userAdmin = User::factory()->create([
            'name' => implode(' ', fake()->words(2)),
            'email' => fake()->email,
            'identify_number' => fake()->uuid(),
            'email_verified_at' => now(),
            'password' => 'MyPassword123'
        ]);
        $this->postJson('/login', [
            'email' => $userAdmin->email,
            'password' => 'MyPassword123'
        ]);

        $project = Project::factory()->create([
            'user_id' => $userAdmin->id,
            'name' => fake()->name,
        ]);
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

        $projectId = $project->id;
        $teamMember = Team::factory()
            ->create([
                'project_id' => $projectId,
                'access' => 0,
                'role_id' => 1,
                'user_id' => $user->id
            ]);
        $inviteIdentify = fake()->uuid();
        Invitation::factory()
            ->create([
                'invite_identify' => $inviteIdentify,
                'member_id' => $teamMember->id,
                'role_id' => 1,
                'project_id' => $projectId,
                'user_id' => $userAdmin->id,
            ]);
        $response = $this->postJson("/api/invitation/accept",
            [
                'invite_identify' => 'cfab17f3-44be-35d5-555-5e704701ef7b',
                'accept' => 0
            ]);
        $response->assertStatus(400);
    }

    public function test_data_is_not_valid()
    {
        $email = fake()->email;
        $identifyNumber = fake()->uuid();
        $userAdmin = User::factory()->create([
            'name' => implode(' ', fake()->words(2)),
            'email' => fake()->email,
            'identify_number' => fake()->uuid(),
            'email_verified_at' => now(),
            'password' => 'MyPassword123'
        ]);
        $this->postJson('/login', [
            'email' => $userAdmin->email,
            'password' => 'MyPassword123'
        ]);

        $project = Project::factory()->create([
            'user_id' => $userAdmin->id,
            'name' => fake()->name,
        ]);
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

        $projectId = $project->id;
        $teamMember = Team::factory()
            ->create([
                'project_id' => $projectId,
                'access' => 0,
                'role_id' => 1,
                'user_id' => $user->id
            ]);
        $inviteIdentify = fake()->uuid();
        Invitation::factory()
            ->create([
                'invite_identify' => $inviteIdentify,
                'member_id' => $teamMember->id,
                'role_id' => 1,
                'project_id' => $projectId,
                'user_id' => $userAdmin->id,
            ]);
        $response = $this->postJson("/api/invitation/accept",
            [
                'invite_identify' => $inviteIdentify,
                'accept' => 'true'
            ]);
        $response->assertStatus(422);
    }
}
