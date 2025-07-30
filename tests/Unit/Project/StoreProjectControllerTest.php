<?php

namespace Project;

use App\Models\RequestHistory;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StoreProjectControllerTest extends TestCase
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

        $this->postJson('/register-complete', [
            'identify_number' => $user->identify_number,
            'name' => 'mohammedMaher',
            'password' => 'MyPassword123',
            'password_confirmation' => 'MyPassword123'
        ]);

        $originalPhotoPath = 'upload/projects_icon/test.png';
        $newIconName = 'test-2.png';
        $destinationPath = 'upload/projects_icon/' . $newIconName;
        Storage::disk('public')->copy($originalPhotoPath, $destinationPath);
        $name = fake()->name;

        $response = $this->postJson('api/projects/create', [
            'name' => $name,
            'key' => substr($name, 0, 2),
            'icon' => new UploadedFile(storage_path('app/public/' . $destinationPath), $newIconName, null, null, true),
        ]);
        $response->assertStatus(201);
    }
}
