<?php

namespace Auth;

use App\Models\User;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    public function test_user_he_has_session_open_LogIn()
    {
        $email = fake()->email;
        $identifyNumber = fake()->uuid();
        User::factory()->create([
            'name' => fake()->name(),
            'email' => $email,
            'identify_number' => $identifyNumber,
            'email_verified_at' => now(),
            'password' => 'MyPassword123'
        ]);
        $this->postJson('/login', [
            'email' => $email,
            'password' => 'MyPassword123'
        ]);
        $response = $this->getJson('/api/user');
        $response->assertStatus(200);
        $this->assertAuthenticated();
    }

    public function test_user_he_not_LogIn_the_session_not_open()
    {
        $email = fake()->email;
        $identifyNumber = fake()->uuid();
        User::factory()->create([
            'name' => fake()->name(),
            'email' => $email,
            'identify_number' => $identifyNumber,
            'email_verified_at' => now(),
            'password' => 'MyPassword123'
        ]);
        $response = $this->getJson('/api/user');
        $response->assertStatus(401);
    }
}
