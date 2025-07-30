<?php

namespace Tests\Unit\Auth;

use App\Models\User;
use Tests\TestCase;

class LogoutControllerTest extends TestCase
{
    public function test_user_can_logout()
    {
        $user = User::factory()->create([
            'email' => fake()->email,
            'identify_number' => fake()->uuid(),
            'name' => 'mahmood',
            'password' => 'MyPassword123',
            'email_verified_at' => now()
        ]);
        $this->postJson('/login', [
            'email' => $user->email,
            'password' => 'MyPassword123',
        ]);
        $response = $this->postJson('api/logout');
        $response->assertStatus(204);
    }

    public function test_user_()
    {
        $response = $this->postJson('api/logout');
        $response->assertStatus(401);
    }
}
