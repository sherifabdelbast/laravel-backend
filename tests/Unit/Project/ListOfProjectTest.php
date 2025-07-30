<?php

namespace Project;

use App\Models\Clipboard;
use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use Tests\TestCase;

class ListOfProjectTest extends TestCase
{
    public function test_success_story()
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

        Team::factory(3)->create([
            'user_id' => $user->id,
        ]);

        Project::factory(3)->create([
            'user_id' => $user->id,
            'name' => fake()->name,
        ]);

        Clipboard::factory(2)
            ->create([
                'user_id' => $user->id
            ]);
        Clipboard::factory(1)
            ->create([
                'user_id' => $user->id,
                'archive' => 1
            ]);
        $response = $this->getJson("/api/projects");
        $response->assertStatus(200);
    }
}
