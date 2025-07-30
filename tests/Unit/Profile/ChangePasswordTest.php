<?php

namespace Tests\Unit\Profile;

use App\Models\User;
use Tests\TestCase;

class ChangePasswordTest extends TestCase
{
    public function test_success_story()
    {
        $email = fake()->email;
        $identifyNumber = fake()->uuid();
        $user = User::factory()->create([
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
        $response = $this->postJson("api/profile/$user->identify_number/change-password",
            [
                'old_password' => 'MyPassword123',
                'password' => 'MyPassword1234',
                'password_confirmation' => 'MyPassword1234',
            ]);

        $response->assertStatus(200);
    }
}
