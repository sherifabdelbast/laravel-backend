<?php

namespace Tests\Unit\Profile;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EditProfileTest extends TestCase
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

        $originalPhotoPath = 'upload/personal_photo/test.png';
        $newPhotoName = 'test-2.png';
        $destinationPath = 'upload/personal_photo/' . $newPhotoName;
        Storage::disk('public')->copy($originalPhotoPath, $destinationPath);

        $response = $this->postJson("api/profile/$user->identify_number/edit",
            [
                'name' => 'mohammed',
                'photo' => new UploadedFile(storage_path('app/public/'
                    . $destinationPath), $newPhotoName, null, null, true),
                'job_title' => 'Dev',
                'skills' => null,
            ]);

        $response->assertStatus(200);
    }

    public function test_success_story_edit_skills()
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

        $skills = '["PHP", "CSS", "JS"]';
        $response = $this->postJson("api/profile/$user->identify_number/edit", [
            'name' => 'mohammed',
            'photo' => null,
            'job_title' => 'Dev',
            'skills' => $skills,
        ]);
        $response->assertStatus(200);
    }

    public function test_data_not_valid()
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


        $response = $this->postJson("api/profile/$user->identify_number/edit", [
            'name' => null,
            'photo' => null,
            'job_title' => 'Dev',
        ]);
        $response->assertStatus(422);
    }
}
