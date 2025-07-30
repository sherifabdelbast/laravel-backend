<?php

namespace Tests\Unit\Issue;

use App\Models\Issue;
use App\Models\Sprint;
use App\Models\Status;
use App\Models\Team;
use App\Models\User;
use Tests\TestCase;

class MoveIssueTest extends TestCase
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
        $project = $this->postJson("api/projects/create",
            [
                'name' => $name,
                'key' => substr($name, 0, 2),
                'user_id' => $user->id
            ]);

        $projectId = $project['project_id'];
        $teamMember = Team::factory()
            ->create([
                'project_id' => $projectId,
            ]);
        $status = Status::query()
            ->where('project_id', '=', $projectId)
            ->first();
        $issue = Issue::factory()
            ->create(
                [
                    'assign_to' => $teamMember->id,
                    'project_id' => $projectId,
                    'status_id' => $status->id,
                    'user_id' => $user->id,
                    'order' => 1
                ]);
        $sprint = Sprint::query()
            ->where('project_id', '=', $projectId)
            ->first();
            $projectIdentify = $project['project_identify'];
        $response = $this->postJson("/api/projects/$projectIdentify/backlog/issues/$issue->id/move",
            [
                'sprint_id' => $sprint->id,
                'order' => 1
            ]);
        $response->assertStatus(200);
    }

    public function test_Invalid()
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
        ]);

        $projectId = $project['project_id'];
        $teamMember = Team::factory()
            ->create([
                'project_id' => $projectId,
            ]);
        $status = Status::query()
            ->where('project_id', '=', $projectId)
            ->first();

        $issue = Issue::factory()
            ->create(
                [
                    'assign_to' => $teamMember->id,
                    'project_id' => $projectId,
                    'sprint_id' => null,
                    'status_id' => $status->id,
                    'user_id' => $user->id,
                    'order' => 1
                ]);
        $sprint = Sprint::query()
            ->where('project_id', '=', $projectId)
            ->first();
        $projectIdentify = $project['project_identify'];
        $response = $this->postJson("/api/projects/$projectIdentify/backlog/issues/$issue->id/move",
            [
                'sprint_id' => 100,
                'order' => 2
            ]);
        $response->assertStatus(422);
    }
}
