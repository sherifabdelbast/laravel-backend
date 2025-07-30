<?php

namespace Auth;

use App\Models\RequestHistory;
use App\Models\User;
use Illuminate\Support\Str;
use Tests\TestCase;

class LoginTest extends TestCase
{
    public function test_success_story()
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
        $response = $this->postJson('/login', [
            'email' => $email,
            'password' => 'MyPassword123'
        ]);
        $response->assertStatus(200);
    }

    public function test_login_with_invalid_data()
    {
        $response = $this->postJson('login', [
            'email' => 'reanna80exampleorg',
            'password' => 'password'
        ]);
        $response
            ->assertStatus(422);
    }

    public function test_login_is_not_true_email()
    {
        $response = $this->postJson('/login', [
            'email' => fake()->email,
            'password' => 'password12'
        ]);
        $response->assertStatus(400);
    }

    public function test_login_is_not_true_password()
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
        $response = $this->postJson('/login', [
            'email' => $email,
            'password' => 'MyPassword'
        ]);
        $response->assertStatus(400);
    }

    public function test_login_existing_unverified_exceeding_the_limit_of_requests_allowed()
    {
        $email = fake()->email;
        User::factory()->create([
            'email' => $email,
            'identify_number' => fake()->uuid()
        ]);
        $user = User::query()
            ->where('email', '=', $email)
            ->first();
        for ($i = 0; $i < 3; $i++) {
            RequestHistory::factory()
                ->create([
                    'identify_number' => $user->identify_number,
                    'expired_at' => now()
                ]);
        }
        $response = $this->postJson('login',
            [
                'email' => $email,
                'password' => 'password12'
            ]);
        $response->assertStatus(429);
    }

    public function test_login_with_email_is_exist_and_not_verified_and_expiry_time_it_is_not_over()
    {
        $email = fake()->email;
        $this->postJson('/join', [
            'email' => $email,
            'identify_number' => fake()->uuid(),
        ]);

        $response = $this->postJson('/login', [
            'email' => $email,
            'password' => 'password12'
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
                'token' => bcrypt(Str::random(10)),
                'expired_at' => now()
            ]);

        $response = $this->postJson('/login', [
            'email' => $email,
            'password' => 'password12'
        ]);
        $response->assertStatus(200);
    }
}
