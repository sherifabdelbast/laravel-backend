<?php

namespace Tests\Unit\Status;

use App\Models\User;
use Tests\TestCase;

class StoreStatusTest extends TestCase
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
        $response = $this->postJson("api/projects/$projectIdentify/statuses/create", [
            'name' => 'to do BackEnd',
            'order' => 3,
        ]);
        $response->assertStatus(201);
    }

    public function test_name_is_already_exists()
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

        $projectIdentify = $project['project_identify'];
        $response = $this->postJson("api/projects/$projectIdentify/statuses/create", [
            'name' => 'to do',
            'order' => 3,
        ]);
        $response->assertStatus(400);
    }
}
