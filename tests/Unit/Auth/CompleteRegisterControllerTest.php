<?php

namespace Tests\Unit\Auth;

use App\Models\RequestHistory;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CompleteRegisterControllerTest extends TestCase
{
    public function test_success_story()
    {
        $user = User::factory()->create([
            'email' => fake()->email,
            'identify_number' => fake()->uuid(),
        ]);
        RequestHistory::factory()
            ->create([
                'identify_number' => $user->identify_number,
                'expired_at' => Carbon::now()->addMinutes(15)
            ]);

        $originalPhotoPath = 'upload/personal_photo/test.png';
        $newPhotoName = 'test-2.png';
        $destinationPath = 'upload/personal_photo/' . $newPhotoName;
        Storage::disk('public')->copy($originalPhotoPath, $destinationPath);

        $response = $this->postJson('/register-complete', [
            'identify_number' => $user->identify_number,
            'name' => 'mohammed maher',
            'photo' => new UploadedFile(storage_path('app/public/'
                . $destinationPath), $newPhotoName, null, null, true),
            'password' => 'MyPassword123',
            'password_confirmation' => 'MyPassword123'
        ]);
        $response->assertStatus(200);
        $this->assertAuthenticated();
    }

    public function test_Everything_is_fine_the_request_time_is_expired()
    {
        $user = User::factory()->create();
        RequestHistory::factory()
            ->create([
                'identify_number' => $user->identify_number,
                'expired_at' => now()
            ]);
        $user = User::query()
            ->where('email', '=', $user->email)
            ->first();

        $response = $this->postJson('/register-complete',
            [
                'identify_number' => $user->identify_number,
                'name' => 'mohammedMaher',
                'password' => 'MyPassword123',
                'password_confirmation' => 'MyPassword123'
            ]);
        $response->assertStatus(400);
    }

    public function test_identify_number_is_not_exists()
    {

        $response = $this->postJson('/register-complete',
            [
                'identify_number' => fake()->uuid,
                'name' => 'mohammedMaher',
                'password' => 'MyPassword123',
                'password_confirmation' => 'MyPassword123'
            ]);
        $response->assertStatus(400);
    }

    public function test_identify_number_is_exists_data_is_not_valid()
    {
        $user = User::factory()->create([
            'email' => fake()->email,
            'identify_number' => fake()->uuid(),
        ]);

        $user = User::query()
            ->where('email', '=', $user->email)
            ->first();

        $response = $this->postJson('/register-complete',
            [
                'identify_number' => $user->identify_number,
                'name' => 'mohammedMaher'
            ]);
        $response->assertStatus(422);
    }
}
