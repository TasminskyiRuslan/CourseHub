<?php

namespace App\Queries\Course\Teacher;

use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

readonly class GetCourseQuery
{
    /**
     * Retrieve detailed information about the specified course for the specified teacher.
     *
     * @param string $courseSlug
     * @param User $teacher
     * @return Course
     * @throws ModelNotFoundException
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
