<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProjectHistory>
 */
class ProjectHistoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'status'=> null,
            'description'=> null,
            'issue_id'=> null,
            'project_id'=> null,
            'user_id'=> null,
        ];
    }
}
