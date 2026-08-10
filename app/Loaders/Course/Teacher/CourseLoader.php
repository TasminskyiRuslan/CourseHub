<?php

declare(strict_types=1);

namespace App\Loaders\Course\Teacher;

use App\Models\Course;

readonly class CourseLoader
{
    /**
     * Eager load relations and counts for the specified teacher's course.
     */
    public function handle(Course $course): Course
    {
        return $course->loadCount([
            'lessons',
        ]);
    }
}
