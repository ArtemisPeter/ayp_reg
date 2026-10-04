<?php

namespace Database\Factories;

use App\Enums\AttendeeType;
use App\Enums\RegistrationType;
use App\Models\Attendee;
use App\Models\LocalChurch;
use App\Models\YouthCamp;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Attendee>
 */
class AttendeeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'youth_camp_id' => YouthCamp::factory(),
            'local_church_id' => LocalChurch::factory(),
            'first_name' => fake()->firstName(),
            'middle_name' => null,
            'last_name' => fake()->lastName(),
            'birthday' => fake()->dateTimeBetween('-40 years', '-13 years')->format('Y-m-d'),
            'is_baptized' => fake()->boolean(),
            'type' => AttendeeType::Youth,
            'registration_type' => RegistrationType::PreRegistration,
        ];
    }
}
