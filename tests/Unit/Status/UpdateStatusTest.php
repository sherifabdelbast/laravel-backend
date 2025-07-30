<?php

namespace Status;

use App\Models\Status;
use App\Models\User;
use Tests\TestCase;

class UpdateStatusTest extends TestCase
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
        $project = $this->postJson("api/projects/create", [
            'name' => $name,
            'key' => substr($name, 0, 2),
            'user_id' => $user->id
        ]);

        $projectId = $project['project_id'];

        $status = Status::query()
        ->where('project_id','=',$projectId)
        ->first();
        $projectIdentify = $project['project_identify'];

        $response = $this->postJson("api/projects/$projectIdentify/statuses/$status->id/edit", [
            'name' => 'To Do BackEnd',
            'max' => 0,
        ]);
        $response->assertStatus(200);
    }
}
