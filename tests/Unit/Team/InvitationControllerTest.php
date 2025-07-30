<?php

namespace Tests\Unit\Team;

use App\Models\Invitation;
use App\Models\Project;
use App\Models\Role;
use App\Models\Team;
use App\Models\User;
use Carbon\Carbon;
use Tests\TestCase;

class InvitationControllerTest extends TestCase
{
    public function test_user_is_not_exists()
    {
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
        $role = Role::query()
            ->where('project_id', '=', $project['project_id'])
            ->first();

        $projectIdentify = $project['project_identify'];
        $response = $this->postJson("/api/projects/$projectIdentify/team/invite", [
            'message' => fake()->paragraph,
            'email' => fake()->email,
            'role_id' => $role->id + 1,
        ]);
        $response->assertStatus(200);
    }

    public function test_exists_is_not_TeamMember()
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
        $role = Role::query()
            ->where('project_id', '=', $project['project_id'])
            ->first();

        $projectIdentify = $project['project_identify'];
        $response = $this->postJson("/api/projects/$projectIdentify/team/invite", [
            'message' => fake()->paragraph,
            'email' => $email,
            'role_id' => $role->id + 1,
        ]);
        $response->assertStatus(200);
    }

    public function test_user_is_teamMember_and_last_Invitation_is_expired()
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
        Invitation::factory()
            ->create([
                'invite_identify' => fake()->uuid(),
                'member_id' => $teamMember->id,
                'role_id' => 1,
                'project_id' => $projectId,
                'user_id' => $userAdmin->id,
                'created_at' => '2023-08-09 15:43:38'
            ]);
        $role = Role::query()
            ->where('project_id', '=', $project['project_id'])
            ->first();
        $projectIdentify = $project['project_identify'];
        $response = $this->postJson("/api/projects/$projectIdentify/team/invite",
            [
                'message' => fake()->paragraph,
                'email' => $email,
                'role_id' => $role->id + 1,
            ]);
        $response->assertStatus(200);
    }

    public function test_user_is_teamMember_and_last_Invitation_is_not_expired()
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
                'user_id' => $user->id
            ]);

        Invitation::factory()
            ->create([
                'invite_identify' => fake()->uuid(),
                'member_id' => $teamMember->id,
                'project_id' => $projectId,
                'user_id' => $userAdmin->id,
            ]);
        $role = Role::query()
            ->where('project_id', '=', $project['project_id'])
            ->first();
        $projectIdentify = $project['project_identify'];
        $response = $this->postJson("/api/projects/$projectIdentify/team/invite", [
            'message' => fake()->paragraph,
            'email' => $email,
            'role_id' => $role->id + 1,
        ]);
        $response->assertStatus(400);
    }

    public function test_user_is_teamMember_and_last_Invitation_is_expired_and_exceeding_the_limit_of_requests_allowed()
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
                'user_id' => $user->id
            ]);

        Invitation::factory(3)
            ->create([
                'invite_identify' => fake()->uuid(),
                'member_id' => $teamMember->id,
                'project_id' => $projectId,
                'user_id' => $userAdmin->id,
                'created_at' => Carbon::now()->subHour()
            ]);

        $role = Role::query()
            ->where('project_id', '=', $project['project_id'])
            ->first();
        $projectIdentify = $project['project_identify'];
        $response = $this->postJson("/api/projects/$projectIdentify/team/invite",
            [
                'message' => fake()->paragraph,
                'email' => $email,
                'role_id' => $role->id + 1,
            ]);
        $response->assertStatus(429);
    }

    public function test_user_is_already_is_TeamMember()
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
        $role = Role::query()
            ->where('project_id', '=', $project['project_id'])
            ->first();
        $projectIdentify = $project['project_identify'];
        $response = $this->postJson("/api/projects/$projectIdentify/team/invite",
            [
                'message' => fake()->paragraph,
                'email' => $email,
                'role_id' => $role->id + 1,
            ]);
        $response->assertStatus(400);
    }

    public function test_user_is_not_teamMember_or_is_not_Admin()
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

        $projectIdentify = Project::query()->inRandomOrder()->value('project_identify');
        $response = $this->postJson("/api/projects/$projectIdentify/team/invite",
            [
                'message' => fake()->paragraph,
                'email' => fake()->email,
                'role_id' => 1
            ]);
        $response->assertStatus(403);
    }

    public function test_data_is_not_valid()
    {
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
        $role = Role::query()
            ->where('project_id', '=', $project['project_id'])
            ->first();
        $projectIdentify = $project['project_identify'];
        $response = $this->postJson("/api/projects/$projectIdentify/team/invite",
            [
                'message' => fake()->paragraph,
                'email' => 'mohammedMaher',
                'role_id' => $role->id + 1,
            ]);
        $response->assertStatus(422);
    }

}
