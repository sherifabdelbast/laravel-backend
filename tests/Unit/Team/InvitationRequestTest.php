<?php

namespace Team;

use App\Models\Invitation;
use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use Tests\TestCase;

class InvitationRequestTest extends TestCase
{
    public function test_success_story()
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
        $response = $this->post("/api/invitation", [
            'invite_identify' => $inviteIdentify
        ]);
        $response->assertStatus(200);
    }

    public function test_teamMember_is_not_complete_register()
    {
        $email = fake()->email;
        $identifyNumber = fake()->uuid();
        $userAdmin = User::factory()->create([
            'name' => 'mohammed',
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
        $this->postJson('api/logout');
        $user = User::factory()->create([
            'name' => 'ahmed',
            'email' => $email,
            'identify_number' => $identifyNumber,
            'email_verified_at' => now(),
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
        $response = $this->post("/api/invitation",
            [
                'invite_identify' => $inviteIdentify
            ]);
        $response->assertStatus(400);
    }

    public function test_teamMember_another_user_not_the_invitee()
    {
        $email = fake()->email;
        $identifyNumber = fake()->uuid();
        $userAdmin = User::factory()->create([
            'name' => 'Mohammed',
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
            'email' => $email,
            'identify_number' => $identifyNumber,
        ]);
        $projectId = $project->id;
        $teamMember = Team::factory()
            ->create([
                'project_id' => $projectId,
                'access' => 1,
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
        $response = $this->post("/api/invitation",
            [
                'invite_identify' => $inviteIdentify
            ]);
        $response->assertStatus(400);
    }

    public function test_request_Invitation_is_not_last_request()
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

        $name = fake()->name;
        $project = $this->postJson("api/projects/create", [
            'name' => $name,
            'key' => substr($name, 0, 2),
            'user_id' => $userAdmin->id
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

        $projectId = $project['project_id'];
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
                'created_at' => '2023-08-09 15:43:38'
            ]);
        Invitation::factory()
            ->create([
                'invite_identify' => fake()->uuid,
                'member_id' => $teamMember->id,
                'role_id' => 1,
                'project_id' => $projectId,
                'user_id' => $userAdmin->id,
            ]);

        $response = $this->post("/api/invitation",
            [
                'invite_identify' => $inviteIdentify
            ]);
        $response->assertStatus(400);
    }

    public function test_Invitation_request_does_not_exists()
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

        $name = fake()->name;
        $project = $this->postJson("api/projects/create", [
            'name' => $name,
            'key' => substr($name, 0, 2),
            'user_id' => $userAdmin->id
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

        $projectId = $project['project_id'];
        Team::factory()
            ->create([
                'project_id' => $projectId,
                'access' => 0,
                'role_id' => 1,
                'user_id' => $user->id
            ]);
        $inviteIdentify = fake()->uuid();

        $response = $this->post("/api/invitation",
            [
                'invite_identify' => $inviteIdentify
            ]);
        $response->assertStatus(400);
    }
}
