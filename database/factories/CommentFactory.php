<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Model>
 */
class CommentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'content'=>fake()->text(),
            'referred_to'=>null,
            'deleted_at'=>null,
            'issue_id'=>null,
            'project_id'=>null,
            'user_id'=>null,
        ];
    }
}
