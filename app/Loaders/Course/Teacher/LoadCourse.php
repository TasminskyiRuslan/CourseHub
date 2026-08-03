<?php

namespace App\Loaders\Course\Teacher;

use App\Models\Course;

readonly class LoadCourse
{
    /**
     * Eager load relations and counts for the specified teacher's course.
     *
     * @param Course $course
     * @return Course
     */
    public function handle(Course $course): Course
    {
        return $course->loadCount([
            'lessons',
        ]);
    }
}
