<?php

namespace Tests\Unit\Backlog;

use App\Models\Issue;
use App\Models\Project;
use App\Models\Sprint;
use App\Models\Status;
use App\Models\Team;
use App\Models\User;
use Tests\TestCase;

class ListOfIssuesInBacklogTest extends TestCase
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
        Issue::factory(3)->create([
            'title' => fake()->title,
            'status_id' => $status->id,
            'project_id' => $projectId,
            'user_id' => $user->id
        ]);
        $sprint = Sprint::query()
            ->where('project_id', '=', $projectId)
            ->first();
        Issue::factory(3)->create([
            'title' => fake()->title,
            'status_id' => $status->id,
            'sprint_id' => $sprint->id,
            'project_id' => $projectId,
            'user_id' => $user->id
        ]);
        Issue::factory(3)->create([
            'title' => fake()->title,
            'status_id' => $status->id,
            'sprint_id' => null,
            'project_id' => $projectId,
            'user_id' => $user->id
        ]);
        $projectIdentify = $project['project_identify'];
        $response = $this->getJson("api/projects/$projectIdentify/backlog");
        $response->assertStatus(200);
    }

    public function test_forbidden()
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

        $project = Project::factory()->create([
            'user_id' => $user->id,
            'name' => fake()->name,
        ]);

        $projectId = $project->id;
        $teamMember = Team::factory()
            ->create([
                'project_id' => $projectId,
            ]);
        $status = Status::query()
            ->create([
                'project_id' => $projectId,
                'name' => 'To Do test',
                'user_id' => $user->id
            ]);

        Issue::factory()
            ->create(
                [
                    'assign_to' => $teamMember->id,
                    'status_id' => $status->id,
                    'project_id' => $projectId,
                    'user_id' => $user->id,

                ]);

        $response = $this->getJson("api/projects/$project->project_identify/backlog");
        $response->assertStatus(403);
    }
}
