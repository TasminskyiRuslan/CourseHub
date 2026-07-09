<?php

namespace App\Actions\Course;

use App\Data\Course\Requests\UpdateCourseData;
use App\Models\Course;

class UpdateCourseAction
{
    /**
     * Update the specified course.
     *
     * @param UpdateCourseData $data
     * @param Course $course
     * @return Course
     */
    public function handle(UpdateCourseData $data, Course $course): Course
    {
        $course->update($data->all());
        return $course;
    }
}
