<?php

namespace Tests\Unit\Auth;

use App\Models\User;
use Tests\TestCase;

class ForgetPasswordTest extends TestCase
{
    public function test_user_forget_password()
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
        $this->postJson('api/logout');

        $response = $this->postJson('forget-password', [
            'email' => $user->email,
        ]);
        $response->assertStatus(200);
    }

    public function test_user_not_found()
    {
        $response = $this->postJson('forget-password', [
            'email' => 'mahmood_333@hotmail.com'
        ]);
        $response->assertStatus(400);
    }

    public function test_user_not_valid()
    {
        $response = $this->postJson('forget-password', [
            'email' => 'mahmood'
        ]);
        $response->assertStatus(422);
    }
}
