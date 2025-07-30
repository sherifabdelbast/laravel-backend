<?php

namespace Tests\Unit\Notification;

use App\Models\User;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class StoreUserPlayerId extends TestCase
{
    public function test_success_story()
    {
        $user = User::factory()->create([
            'name' => implode(' ', fake()->words(2)),
            'email' => fake()->email,
            'identify_number' => fake()->uuid(),
            'email_verified_at' => now(),
            'password' => 'MyPassword123'
        ]);
        $this->postJson('/login', [
            'email' => $user->email,
            'password' => 'MyPassword123'
        ]);
        $response = $this->postJson('/api/player-id',[
            'player_id' => fake()->uuid()
        ]);
        $response->assertStatus(200);
    }
}
