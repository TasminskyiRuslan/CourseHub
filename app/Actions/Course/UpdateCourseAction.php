<?php

namespace App\Actions\Course;

use App\Data\Course\Requests\UpdateCourseData;
use App\Models\Course;
use Illuminate\Support\Facades\DB;
use Throwable;

class UpdateCourseAction
{
    /**
     * Update the specified course.
     *
     * @param UpdateCourseData $courseData
     * @param Course $course
     * @return Course
     * @throws Throwable
     */
    public function handle(UpdateCourseData $courseData, Course $course): Course
    {
        return DB::transaction(function () use ($courseData, $course) {
            $course->update($courseData->all());
            return $course;
        });
    }
}
