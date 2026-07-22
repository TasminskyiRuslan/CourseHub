<?php

namespace App\Actions\Course;

use App\Models\Course;
use App\Models\User;
use App\Queries\Course\Public\GetCourseQuery;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

readonly class EnrollUserAction
{
    /**
     * Enroll the specified user in the specified course.
     *
     * @param User $user
     * @param Course $course
     * @return Course
     */
    public function handle(User $user, Course $course): Course
    {
        $user->enrolledCourses()->syncWithoutDetaching([
            $course->id
        ]);

        return $course;
    }
}
