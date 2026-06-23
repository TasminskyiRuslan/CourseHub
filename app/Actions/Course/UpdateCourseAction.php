<?php

namespace App\Actions\Course;

use App\Data\Course\Requests\UpdateCourseData;
use App\Models\Course;

class UpdateCourseAction
{
    /**
     * Update the specified course.
     *
     * @param UpdateCourseData $courseData
     * @param Course $course
     * @return Course
     */
    public function handle(UpdateCourseData $courseData, Course $course): Course
    {
        $course->update($courseData->all());
        return $course;
    }
}
