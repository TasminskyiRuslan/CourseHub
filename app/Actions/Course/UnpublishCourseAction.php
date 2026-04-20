<?php

namespace App\Actions\Course;

use App\Models\Course;

class UnpublishCourseAction
{
    /**
     * Unpublish the specified course.
     *
     * @param Course $course
     * @return Course
     */
    public function handle(Course $course): Course
    {
        $course->unpublish()->save();
        return $course;
    }
}
