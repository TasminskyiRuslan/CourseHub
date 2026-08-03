<?php

namespace App\Finders\Course\Student;

use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

readonly class FindCourseBySlug
{
    /**
     * Find the specified enrolled active course by slug for a student.
     *
     * @param User $user
     * @param string $slug
     * @return Course
     * @throws ModelNotFoundException
     */
    public function handle(User $user, string $slug): Course
    {
        return $user->enrolledCourses()
            ->active()
            ->where('slug', $slug)
            ->firstOrFail();
    }
}
