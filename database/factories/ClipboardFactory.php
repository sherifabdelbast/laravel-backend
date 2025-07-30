<?php

namespace Database\Factories;

use App\Models\Clipboard;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Clipboard>
 */
class ClipboardFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => null,
            'project_id' => Team::query()->inRandomOrder()->value('project_id'),
            'favorite' => 0
        ];
    }
}
