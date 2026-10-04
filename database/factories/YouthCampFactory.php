<?php

namespace Database\Factories;

use App\Models\YouthCamp;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<YouthCamp>
 */
class YouthCampFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'year' => fake()->unique()->numberBetween(2020, 2090),
            'theme' => fake()->sentence(3),
            'starts_on' => '2026-12-26',
            'ends_on' => '2026-12-29',
        ];
    }
}
