<?php

namespace App\Actions\Course;

use App\Models\Course;
use App\Models\User;

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
