<?php

namespace Tests\Unit\Status;

use App\Models\Issue;
use App\Models\Status;
use App\Models\User;
use Tests\TestCase;

class DeleteStatusTest extends TestCase
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

        $projectIdentify = $project['project_identify'];

        $ss = $this->postJson("api/projects/$projectIdentify/statuses/create", [
            'name' => 'To Do BackEnd',
            'order' => 3,
        ]);
        $status = Status::query()
            ->where('project_id', '=', $project['project_id'])
            ->latest('id')
            ->first();
        Issue::factory(5)
            ->create([
                'project_id' => $project['project_id'],
                'status_id' => $status->id,
                'user_id' => $user->id,
                'sprint_id' => null
            ]);
        $response = $this->postJson("api/projects/$projectIdentify/statuses/$status->id/delete", [
            'issues_status_id' => $status->id - 2
        ]);
        $response->assertStatus(200);
    }

    public function test_delete_status_only_one_of_its_type()
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
        ]);

        $projectId = $project['project_id'];
        $status = Status::query()
            ->where('project_id', '=', $projectId)
            ->first();

        $newStatusId = $status->id + 1;
        $projectIdentify = $project['project_identify'];
        $response = $this->postJson("api/projects/$projectIdentify/statuses/$status->id/delete", [
            'issues_status_id' => $newStatusId
        ]);
        $response->assertStatus(400);
    }
}
