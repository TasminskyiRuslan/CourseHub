<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Database\Seeder;

class LessonSeeder extends Seeder
{
    /**
     * Seed the lessons table.
     */
    public function run(): void
    {
        Course::all()->each(function (Course $course) {
            $lessonsCount = fake()->numberBetween(3, 8);

            Lesson::factory()
                ->count($lessonsCount)
                ->for($course)
                ->create();
        });
    }
}
