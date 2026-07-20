<?php

namespace App\Actions\Course;

use App\Data\Course\Requests\CreateCourseData;
use App\Models\Course;
use App\Models\User;

class CreateCourseAction
{
    /**
     * Create a new course for the specified teacher.
     *
     * @param CreateCourseData $data
     * @param User $teacher
     * @return Course
     */
    public function handle(CreateCourseData $data, User $teacher): Course
    {
        return $teacher->courses()->create($data->all());
    }
}
