<?php

namespace App\Actions\Course;

use App\Models\Course;

class PublishCourseAction
{
    /**
     * Publish the specified course.
     *
     * @param Course $course
     * @return Course
     */
    public function handle(Course $course): Course
    {
        $course->publish()->save();
        return $course;
    }
}
