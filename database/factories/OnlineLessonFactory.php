<?php

namespace Database\Factories;

use App\Models\OnlineLesson;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OnlineLesson>
 */
class OnlineLessonFactory extends Factory
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
            'meeting_link' => fake()->url(),
        ];
    }
}
