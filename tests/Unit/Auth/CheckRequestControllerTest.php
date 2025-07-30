<?php

namespace Tests\Unit\Auth;

use App\Models\RequestHistory;
use App\Models\User;
use Illuminate\Support\Str;
use Tests\TestCase;

class CheckRequestControllerTest extends TestCase
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

        $request = RequestHistory::factory()
            ->create([
                'identify_number' => $identifyNumber,
                'token' => Str::random(10),
                'expired_at' => now()->addMinutes(15)
            ]);

        $response = $this->postJson('/check-token',
            [
                'token' => $request->token
            ]);
        $response->assertStatus(200);
    }

    public function test_identifyNumber_is_not_true()
    {
        $response = $this->postJson('/check-token',
            [
                'token' => '6f7a5171-9999-3181-8032-5ed2a7661152'
            ]);
        $response->assertStatus(403);
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
                'token' => Str::random(10),
                'expired_at' => now()
            ]);

        $response = $this->postJson('/welcome',
            [
                'identify_number' => $identifyNumber
            ]);
        $response->assertStatus(400);
    }
}
