<?php

namespace Tests\Unit\Issue;

use App\Models\Issue;
use App\Models\Status;
use App\Models\User;
use Tests\TestCase;

class BoardListIssueTest extends TestCase
{
    public function test_success_story()
    {
        $user = User::factory()->create([
            'name' => implode('', fake()->words(2)),
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

        $projectId = $project['project_id'];

        $status = Status::query()
            ->where('project_id','=',$projectId)
            ->first();
        Issue::factory(2)->create([
            'title' => fake()->title,
            'status_id' => $status->id,
            'deleted_at' => null,
            'project_id' => $projectId,
            'user_id' => $user->id
        ]);
        $projectIdentify = $project['project_identify'];
        $response = $this->getJson("/api/projects/$projectIdentify/board/");
        $response->assertStatus(200);
    }
}
