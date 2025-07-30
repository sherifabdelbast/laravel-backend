<?php

namespace Project;

use App\Models\Clipboard;
use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use Tests\TestCase;

class FavoriteProjectTest extends TestCase
{
    public function test_success_story_favorite_project_created()
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
        $response = $this->getJson("api/projects/$projectIdentify/favorite");
        $response->assertStatus(200);
    }

    public function test_success_story_favorite_project_invited()
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
            'email' => $email,
            'identify_number' => $identifyNumber,
            'email_verified_at' => now(),
            'password' => 'MyPassword123'
        ]);
        $this->postJson('/login', [
            'email' => $email,
            'password' => 'MyPassword123'
        ]);

        Team::factory()
            ->create([
                'project_id' => $projectId,
                'access' => 1,
                'user_id' => $user->id
            ]);
            $projectIdentify = $project['project_identify'];
        $response = $this->getJson("api/projects/$projectIdentify/favorite");
        $response->assertStatus(200);
    }

    public function test_success_story_favorite_project_invited_and_reject_invitation()
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

        $projectId = $project->id;
        Team::factory()
            ->create([
                'project_id' => $projectId,
                'access' => 0,
                'user_id' => $user->id
            ]);
            $projectIdentify = $project['project_identify'];
        $response = $this->getJson("api/projects/$projectIdentify/favorite");
        $response->assertStatus(403);
    }

    public function test_unFavorite_project_invited()
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

        $projectId = $project->id;
        Team::factory()
            ->create([
                'project_id' => $projectId,
                'access' => 1,
                'user_id' => $user->id
            ]);
        Clipboard::query()
            ->create([
                'project_id' => $projectId,
                'user_id' => $user->id,
                'favorite' => 1
            ]);
            $projectIdentify = $project['project_identify'];
        $response = $this->getJson("api/projects/$projectIdentify/favorite");
        $response->assertStatus(200);
    }
}
