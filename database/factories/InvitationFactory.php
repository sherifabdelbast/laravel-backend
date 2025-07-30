<?php

namespace Database\Factories;

use App\Models\Invitation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Invitation>
 */
class InvitationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return
            [
                'invite_identify'=>fake()->uuid(),
                'message' => fake()->paragraph,
                'member_id' => null,
                'role_id' => 1,
                'project_id' => null,
                'user_id' => null,
            ];
    }
}
