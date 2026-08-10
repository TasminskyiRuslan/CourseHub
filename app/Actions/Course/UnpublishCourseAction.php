<?php

declare(strict_types=1);

namespace App\Actions\Course;

use App\Models\Course;

readonly class UnpublishCourseAction
{
    /**
     * Unpublish the specified course.
     */
    public function handle(Course $course): Course
    {
        $course->unpublish()->save();

        return $course;
    }
}
