<?php

namespace App\Actions\Course;

use App\Models\Course;

readonly class PublishCourseAction
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
