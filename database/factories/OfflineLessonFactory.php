<?php

namespace Database\Factories;

use App\Models\OfflineLesson;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OfflineLesson>
 */
class OfflineLessonFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'start_time' => fake()->dateTimeBetween('+1 days', '+1 week'),
            'end_time' => fake()->dateTimeBetween('+1 week', '+2 weeks'),
            'address' => fake()->address(),
            'room_number' => fake()->bothify('Room ##'),
        ];
    }
}
