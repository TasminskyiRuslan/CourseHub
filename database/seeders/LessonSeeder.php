<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Database\Seeder;

class LessonSeeder extends Seeder
{
    /**
     * Seed the lessons.php table.
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
