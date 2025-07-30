<?php

namespace Tests\Unit\Auth;

use App\Models\RequestHistory;
use App\Models\User;
use Tests\TestCase;

class CheckWelcomeControllerTest extends TestCase
{
    public function test_success_story()
    {
        $email = fake()->email;
        $identifyNumber = fake()->uuid();
        User::factory()
            ->create([
                'email' => $email,
                'identify_number' => $identifyNumber,
            ]);

        RequestHistory::factory()
            ->create([
                'identify_number' => $identifyNumber,
                'expired_at' => now()->addMinutes(15)
            ]);

        $response = $this->postJson('/welcome',
            [
                'identify_number' => $identifyNumber
            ]);
        $response->assertStatus(200);
    }

    public function test_identifyNumber_is_not_exists()
    {
        $response = $this->postJson('/welcome',
            [
                'identify_number' => '6f7a5171-9999-3181-8032-5ed2a7661152'

            ]);
        $response->assertStatus(400);
    }

    public function test_identifyNumber_is_exists_but_request_is_expired()
    {
        $email = fake()->email;
        $identifyNumber = fake()->uuid();
        User::factory()
            ->create([
                'email' => $email,
                'identify_number' => $identifyNumber,
            ]);

        RequestHistory::factory()
            ->create([
                'identify_number' => $identifyNumber,
                'expired_at' => now()
            ]);

        $response = $this->postJson('/welcome',
            [
                'identify_number' => $identifyNumber
            ]);
        $response->assertStatus(400);
    }
}
