<?php

namespace Database\Factories;

use App\Models\Issue;
use Exception;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Issue>
 */
class IssueFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     * @throws Exception
     */
    public function definition(): array
    {
        return
            [
                'title' => fake()->title,
                'description' => null,
                'assign_to' => null,
                'type' => "task",
                'estimated_at' => [0, 0, 0],
                'order_by_status' => random_int(1,5),
                'order' => random_int(1,5),
                'deleted_at' => null,
                'project_id' => null,
                'user_id' => null,
            ];
    }
}
