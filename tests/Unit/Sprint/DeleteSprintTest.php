<?php

namespace Tests\Unit\Sprint;

use App\Models\Issue;
use App\Models\Sprint;
use App\Models\Status;
use App\Models\User;
use Tests\TestCase;

class DeleteSprintTest extends TestCase
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
        $projectId = $project['project_id'];
        $sprint = Sprint::query()
            ->where('project_id','=',$projectId)
            ->first();

        $status = Status::query()
            ->where('project_id', '=', $projectId)
            ->first();

        Issue::factory(5)
            ->create([
                'project_id' => $projectId,
                'status_id' => $status->id,
                'user_id' => $user->id,
                'sprint_id' => $sprint->id
            ]);

        Issue::factory(3)
            ->create([
                'sprint_id' => $sprint->id,
                'project_id' => $projectId,
                'status_id' => $status->id + 2,
                'user_id' => $user->id,
            ]);
            $projectIdentify = $project['project_identify'];
        $response = $this->postJson("/api/projects/$projectIdentify/backlog/sprints/$sprint->id/delete",[
            'issue_sprint_id' => null
        ]);
        $response->assertStatus(200);
    }
}
