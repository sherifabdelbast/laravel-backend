<?php

namespace Database\Factories;

use App\Models\RequestHistory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<RequestHistory>
 */
class RequestHistoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'identify_number' => null,
            'token' => Str::random(10),
            'expired_at' =>  null
        ];
    }
}
