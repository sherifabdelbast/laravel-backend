<?php

namespace Auth;

use App\Models\RequestHistory;
use App\Models\User;
use Illuminate\Support\Str;
use Tests\TestCase;

class JoinTest extends TestCase
{
    /**
     * A basic unit test example.
     */
    public function test_success_story()
    {
        $response = $this->postJson('join', [
            'email' => fake()->email
        ]);
        $response->assertStatus(201);
    }

    public function test_join_with_invalid_data()
    {
        $response = $this->postJson('/join', [
            'email' => 'mahmoodexamplecom885554'
        ]);
        $response
            ->assertStatus(422);
    }

    public function test_existing_unverified_exceeding_the_limit_of_requests_allowed()
    {
        $email = fake()->email;
        User::factory()->create([
            'email' => $email,
            'identify_number' => fake()->uuid()
        ]);
        $user = User::query()
            ->where('email', '=', $email)
            ->first();
            RequestHistory::factory(3)
                ->create([
                    'identify_number' => $user->identify_number,
                    'token' => Str::random(10),
                    'expired_at' => now()
                ]);
        $response = $this->postJson('/join', ['email' => $email]);
        $response->assertStatus(429);
    }

    public function test_join_with_email_is_exist_and_not_verified_and_expiry_time_it_is_not_over()
    {
        $email = fake()->email;
        $this->postJson('join', [
            'email' => $email
        ]);

        $response = $this->postJson('/join', [
            'email' => $email
        ]);
        $response->assertStatus(400);
    }

    public function test_join_with_email_is_exist_and_not_verified_and_expiry_time_it_is_over()
    {
        $email = fake()->email;
        $identifyNumber = fake()->uuid();
        User::factory()->create([
            'email' => $email,
            'identify_number' => $identifyNumber
        ]);
        RequestHistory::query()
            ->create([
                'identify_number' => $identifyNumber,
                'token' => Str::random(10),
                'expired_at' => now()
            ]);

        $response = $this->postJson('/join', [
            'email' => $email
        ]);
        $response->assertStatus(200);
    }

    public function test_join_with_email_is_exists_and_verified()
    {
        $email = fake()->email;
        User::factory()->create([
            'email' => $email,
            'identify_number' => fake()->uuid(),
            'email_verified_at' => now()
        ]);
        $response = $this->postJson('/join', [
            'email' => $email
        ]);
        $response->assertStatus(400);
    }
}
