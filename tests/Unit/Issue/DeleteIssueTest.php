<?php

namespace Tests\Unit\Issue;

use App\Models\Issue;
use App\Models\Status;
use App\Models\User;
use Tests\TestCase;

class DeleteIssueTest extends TestCase
{
    public function test_success_story_delete_issue()
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
        $issue = Issue::factory()
            ->create([
                'assign_to' => null,
                'description' => fake()->paragraph,
                'status_id' => $status->id,
                'deleted_at' => null,
                'project_id' => $projectId,
                'user_id' => $userAdmin->id,
            ]);
            $projectIdentify = $project['project_identify'];
        $response = $this->postJson("/api/projects/$projectIdentify/backlog/issues/$issue->id/delete");
        $response->assertStatus(200);
    }
}
