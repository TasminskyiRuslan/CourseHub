<?php

namespace App\Queries\Course\Teacher;

use App\Models\Course;
use App\Models\User;

class GetCourseQuery
{
    /**
     * Retrieve detailed information about the specified course for the specified teacher.
     *
     * @param string $courseSlug
     * @param User $teacher
     * @return Course
     */
    public function handle(string $courseSlug, User $teacher): Course
    {
        return Course::query()
            ->whereBelongsTo($teacher, 'author')
            ->where('slug', $courseSlug)
            ->withCount(['lessons'])
            ->firstOrFail();
    }
}
