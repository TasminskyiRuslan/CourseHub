<?php

namespace Database\Factories;

use App\Enums\CourseType;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\OfflineLesson;
use App\Models\OnlineLesson;
use App\Models\VideoLesson;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Lesson>
 */
class LessonFactory extends Factory
{
    protected static $order = 1;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->sentence(3);
        return [
            'course_id' => Course::factory()->lazy(),
            'title' => $title,
            'slug' => Str::slug($title),
            'position' => self::$order++,
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (Lesson $lesson) {
            if (!$lesson->lessonable_id) {
                $lessonable = match ($lesson->course->type) {
                    CourseType::OFFLINE => OfflineLesson::factory()->create(),
                    CourseType::ONLINE => OnlineLesson::factory()->create(),
                    CourseType::VIDEO => VideoLesson::factory()->create(),
                };

                $lesson->lessonable()->associate($lessonable);
            }
        });
    }


    public function offline(): static
    {
        return $this->afterCreating(function (Lesson $lesson) {
            $lesson->lessonable()->save(OfflineLesson::factory()->create());
        });
    }

    public function online(): static
    {
        return $this->afterCreating(function (Lesson $lesson) {
            $lesson->lessonable()->save(OnlineLesson::factory()->create());
        });
    }

    public function video(): static
    {
        return $this->afterCreating(function (Lesson $lesson) {
            $lesson->lessonable()->save(VideoLesson::factory()->create());
        });
    }
}
