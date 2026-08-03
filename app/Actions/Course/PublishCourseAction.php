<?php

namespace App\Actions\Course;

use App\Models\Course;
use Illuminate\Support\Facades\DB;
use Throwable;

readonly class PublishCourseAction
{
    /**
     * Publish the specified course.
     *
     * @param Course $course
     * @return Course
     * @throws Throwable
     */
    public function handle(Course $course): Course
    {
        return DB::transaction(function () use ($course) {
            $course->publish()->save();

            return $course;
        });
    }
}
