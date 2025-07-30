<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\IssueHistory>
 */
class IssueHistoryFactory extends Factory
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
            'old_data'=> null,
            'new_data'=> null,
            'issue_id'=> null,
            'user_id'=> null,
        ];
    }
}
