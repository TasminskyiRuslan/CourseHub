<?php

namespace App\Finders\Course\Teacher;

use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

readonly class FindCourseBySlug
{
    /**
     * Find the specified course by slug for a teacher.
     *
     * @param User $teacher
     * @param string $slug
     * @return Course
     * @throws ModelNotFoundException
     */
    public function handle(User $teacher, string $slug): Course
    {
        return $teacher->courses()
            ->where('slug', $slug)
            ->firstOrFail();
    }
}
