<?php

namespace Database\Factories;

use App\Models\Circuit;
use App\Models\LocalChurch;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LocalChurch>
 */
class LocalChurchFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'circuit_id' => Circuit::factory(),
            'name' => fake()->unique()->company().' Church',
        ];
    }
}
