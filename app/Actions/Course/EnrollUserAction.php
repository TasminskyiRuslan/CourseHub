<?php

declare(strict_types=1);

namespace App\Actions\Course;

use App\Models\Course;
use App\Models\User;

readonly class EnrollUserAction
{
    /**
     * Enroll the specified user in the specified course.
     */
    public function handle(User $user, Course $course): Course
    {
        $user->enrolledCourses()->syncWithoutDetaching([
            $course->id,
        ]);

        $course->sendEnrollmentNotification($user);

        return $course;
    }
}
