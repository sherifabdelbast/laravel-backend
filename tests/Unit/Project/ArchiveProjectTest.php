<?php

namespace Tests\Unit\Project;

use App\Models\Clipboard;
use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use Tests\TestCase;

class ArchiveProjectTest extends TestCase
{
    public function test_success_story()
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
        $project = $this->postJson("api/projects/create",
            [
                'name' => $name,
                'key' => substr($name, 0, 2),
                'user_id' => $userAdmin->id
            ]);

        $projectIdentify = $project['project_identify'];

        $response = $this->getJson("api/projects/$projectIdentify/archive");
        $response->assertStatus(200);
    }

// TODO::
    public function test_unClose_project()
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
        $projectIdentify = $project['project_identify'];

        Clipboard::query()
            ->create([
                'project_id' => $project['project_id'],
                'user_id' => $userAdmin->id,
                'archive' => 1
            ]);

        $response = $this->getJson("api/projects/$projectIdentify/archive");
        $response->assertStatus(403);
    }

    public function test_success_story_close_project_invited()
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

        $project = Project::factory()->create([
            'user_id' => $userAdmin->id,
            'name' => fake()->name,
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

        $projectIdentify = $project->project_identify;
        Team::factory()
            ->create([
                'project_id' => $project->id,
                'access' => 1,
                'user_id' => $user->id
            ]);
        $response = $this->getJson("api/projects/$projectIdentify/archive");
        $response->assertStatus(200);
    }

    public function test_unClose_project_invited()
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

        $project = Project::factory()->create([
            'user_id' => $userAdmin->id,
            'name' => fake()->name,
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

        $projectIdentify = $project->project_identify;
        Team::factory()
            ->create([
                'project_id' => $project->id,
                'access' => 1,
                'user_id' => $user->id
            ]);
        Clipboard::query()
            ->create([
                'project_id' => $project->id,
                'user_id' => $user->id,
                'archive' => 1
            ]);
        $response = $this->getJson("api/projects/$projectIdentify/archive");
        $response->assertStatus(403);
    }

    public function test_forbidden()
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
        $project = Project::factory()->create([
            'name' => fake()->name,
            'key' => substr($name, 0, 2),
            'user_id' => User::query()->inRandomOrder()->value('id')
        ]);
        Team::factory()
            ->create([
                'role_id' => 3,
                'project_id' => $project->id,
                'user_id' => $userAdmin->id
            ]);
        $projectIdentify = $project['project_identify'];
        $response = $this->getJson("api/projects/$projectIdentify/archive");
        $response->assertStatus(403);
    }
}
