<?php

declare(strict_types=1);

namespace App\Actions\Course;

use App\Models\Course;

readonly class PublishCourseAction
{
    /**
     * Publish the specified course.
     */
    public function handle(Course $course): Course
    {
        $course->publish()->save();

        return $course;
    }
}
