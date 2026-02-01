<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\OfflineLesson;
use App\Models\OnlineLesson;
use App\Models\VideoLesson;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lesson>
 */
class LessonFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'course_id' => Course::factory()->lazy(),
            'title' => fake()->sentence(3),
            'slug' => null,
            'position' => null,
        ];
    }

    public function offline(): Factory|LessonFactory
    {
        return $this->afterCreating(function (Lesson $lesson) {
            $offlineContent = OfflineLesson::factory()->create();
            $lesson->lessonable()->save($offlineContent);
        });
    }

    public function online(): Factory|LessonFactory
    {
        return $this->afterCreating(function (Lesson $lesson) {
            $onlineContent = OnlineLesson::factory()->create();
            $lesson->lessonable()->save($onlineContent);
        });
    }

    public function video(): Factory|LessonFactory
    {
        return $this->afterCreating(function (Lesson $lesson) {
            $videoContent = VideoLesson::factory()->create();
            $lesson->lessonable()->save($videoContent);
        });
    }
}
