<?php

namespace Tests\Unit\Issue;

use App\Models\Issue;
use App\Models\Project;
use App\Models\Status;
use App\Models\Team;
use App\Models\User;
use Tests\TestCase;

class UpdateIssueTest extends TestCase
{
    public function test_success_story_update_assignTo()
    {
        $email = fake()->email;
        $identifyNumber = fake()->uuid();
        $userAdmin = User::factory()->create([
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
        $project = $this->postJson("api/projects/create",
            [
                'name' => $name,
                'key' => substr($name, 0, 2),
                'user_id' => $userAdmin->id
            ]);
        $projectId = $project['project_id'];

        $user = User::factory()->create([
            'name' => implode(' ', fake()->words(2)),
            'email' => fake()->email,
            'identify_number' => fake()->uuid,
            'email_verified_at' => now(),
            'password' => 'MyPassword123'
        ]);
        $status = Status::query()
            ->create([
                'project_id' => $projectId,
                'name' => 'To Do test',
                'user_id' => $user->id
            ]);

        $Issue = Issue::factory()
            ->create(
                [
                    'assign_to' => null,
                    'description' => fake()->paragraph,
                    'status_id' => $status->id,
                    'project_id' => $projectId,
                    'user_id' => $userAdmin->id,

                ]);
        $team = Team::query()
            ->where('user_id', '=', $userAdmin->id)
            ->first();
        $projectIdentify = $project['project_identify'];
        $response = $this->postJson("/api/projects/$projectIdentify/backlog/issues/$Issue->id/edit",
            [
                'assign_to' => $team->id,
            ]);
        $response->assertStatus(200);
    }

    public function test_success_story_update_description()
    {
        $email = fake()->email;
        $identifyNumber = fake()->uuid();
        $userAdmin = User::factory()->create([
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
        $project = $this->postJson("api/projects/create",
            [
                'name' => $name,
                'key' => substr($name, 0, 2),
                'user_id' => $userAdmin->id
            ]);
        $projectId = $project['project_id'];
        $status = Status::query()
            ->where('project_id', '=', $projectId)
            ->first();

        $Issue = Issue::factory()
            ->create(
                [
                    'assign_to' => null,
                    'description' => fake()->paragraph,
                    'status_id' => $status->id,
                    'project_id' => $projectId,
                    'user_id' => $userAdmin->id,

                ]);
        $projectIdentify = $project['project_identify'];

        $response = $this->postJson("/api/projects/$projectIdentify/backlog/issues/$Issue->id/edit",
            [
                'description' => 'MohammedMaher',
            ]);
        $response->assertStatus(200);
    }

    public function test_success_story_update_type()
    {
        $email = fake()->email;
        $identifyNumber = fake()->uuid();
        $userAdmin = User::factory()->create([
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
        $project = $this->postJson("api/projects/create",
            [
                'name' => $name,
                'key' => substr($name, 0, 2),
                'user_id' => $userAdmin->id
            ]);
        $projectId = $project['project_id'];

        $status = Status::query()
            ->where('project_id', '=', $projectId)
            ->first();
        $Issue = Issue::factory()
            ->create([
                'assign_to' => null,
                'description' => fake()->paragraph,
                'status_id' => $status->id,
                'project_id' => $projectId,
                'user_id' => $userAdmin->id,

            ]);
        $projectIdentify = $project['project_identify'];

        $response = $this->postJson("/api/projects/$projectIdentify/backlog/issues/$Issue->id/edit",
            [
                'type' => 'bug',
            ]);
        $response->assertStatus(200);
    }

//    public function test_success_story_update_status()
//    {
//        $email = fake()->email;
//        $identifyNumber = fake()->uuid();
//        $userAdmin = User::factory()->create([
//            'name' => implode(' ', fake()->words(2)),
//            'email' => $email,
//            'identify_number' => $identifyNumber,
//            'email_verified_at' => now(),
//            'password' => 'MyPassword123'
//        ]);
//        $this->postJson('/login', [
//            'email' => $email,
//            'password' => 'MyPassword123'
//        ]);
//
//        $name = fake()->name;
//        $project = $this->postJson("api/projects/create", [
//            'name' => $name,
//            'key' => substr($name, 0, 2),
//            'user_id' => $userAdmin->id
//        ]);
//
//        $projectId = $project['project_id'];
//
//        $status = Status::query()
//            ->where('project_id', '=', $projectId)
//            ->first();
//        $issue = Issue::factory()
//            ->create([
//                'status_id' => $status->id,
//                'project_id' => $projectId,
//                'user_id' => $userAdmin->id,
//            ]);
//
//        $status = Status::query()
//            ->where('project_id', '=', $projectId)
//            ->first();
//
//        $response = $this->postJson("/api/projects/$projectId/backlog/issues/$issue->id/edit", [
//            'status_id' => $status->id + 1,
//        ]);
//
////        dump($response->json());
//        $response->assertStatus(200);
//    }

    public function test_success_story_update_estimatedAt()
    {
        $email = fake()->email;
        $identifyNumber = fake()->uuid();
        $userAdmin = User::factory()->create([
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
        $project = $this->postJson("api/projects/create",
            [
                'name' => $name,
                'key' => substr($name, 0, 2),
                'user_id' => $userAdmin->id
            ]);
        $projectId = $project['project_id'];

        Team::factory()
            ->create(
                [
                    'project_id' => $projectId,
                ]);
        $status = Status::query()
            ->where('project_id', '=', $projectId)
            ->first();
        $Issue = Issue::factory()
            ->create(
                [
                    'estimated_at' => [12, 2, 5],
                    'description' => fake()->paragraph,
                    'status_id' => $status->id,
                    'project_id' => $projectId,
                    'user_id' => $userAdmin->id,

                ]);
        $projectIdentify = $project['project_identify'];

        $response = $this->postJson("/api/projects/$projectIdentify/backlog/issues/$Issue->id/edit",
            [
                'estimated_at' => [0, 5, 0],

            ]);
        $response->assertStatus(200);
    }

    public function test_data_is_not_valid()
    {
        $email = fake()->email;
        $identifyNumber = fake()->uuid();
        $userAdmin = User::factory()->create([
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
        $project = $this->postJson("api/projects/create",
            [
                'name' => $name,
                'key' => substr($name, 0, 2),
                'user_id' => $userAdmin->id
            ]);

        $projectId = $project['project_id'];
        $teamMember = Team::factory()
            ->create(
                [
                    'project_id' => $projectId,
                ]);
        $status = Status::query()
            ->where('project_id', '=', $projectId)
            ->first();

        $Issue = Issue::factory()
            ->create(
                [
                    'assign_to' => $teamMember->id,
                    'description' => fake()->paragraph,
                    'status_id' => $status->id,
                    'project_id' => $projectId,
                    'user_id' => $userAdmin->id,

                ]);
        $projectIdentify = $project['project_identify'];

        $response = $this->postJson("/api/projects/$projectIdentify/backlog/issues/$Issue->id/edit",
            [
                'assign_to' => 'MohammedMaher',
            ]);
        $response->assertStatus(422);
    }

    public function test_Forbidden()
    {
        $email = fake()->email;
        $identifyNumber = fake()->uuid();
        $userAdmin = User::factory()->create([
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
            ->create(
                [
                    'project_id' => $projectId,
                ]);
        $status = Status::query()
            ->where('project_id', '=', $projectId)
            ->first();
        $Issue = Issue::factory()
            ->create(
                [
                    'assign_to' => $teamMember->id,
                    'description' => fake()->paragraph,
                    'status_id' => $status->id,
                    'project_id' => $projectId,
                    'user_id' => $userAdmin->id,

                ]);

        $projectIdentify = $project['project_identify'].'54';

        $response = $this->postJson("/api/projects/$projectIdentify/backlog/issues/$Issue->id/edit",
            [
                'description' => 'MohammedMaher',
            ]);
        $response->assertStatus(403);
    }
}
