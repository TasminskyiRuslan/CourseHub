<?php

namespace Database\Factories;

use App\Models\VideoLesson;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VideoLesson>
 */
class VideoLessonFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'video_url' => fake()->url(),
            'provider' => fake()->randomElement(['youtube', 'vimeo']),
        ];
    }
}
