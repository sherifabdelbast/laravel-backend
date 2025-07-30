<?php

namespace Tests\Unit\Comment;

use App\Models\Issue;
use App\Models\Status;
use App\Models\User;
use Tests\TestCase;

class CreateCommentTest extends TestCase
{
    public function test_success_story()
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
                $projectIdentify = $project['project_identify'];
        $response = $this->postJson("/api/projects/$projectIdentify/backlog/issues/$Issue->id/comments/create",
            [
                'content' => fake()->text(),
            ]);
        $response->assertStatus(201);
    }
}
