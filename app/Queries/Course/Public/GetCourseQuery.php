<?php

namespace App\Queries\Course\Public;

use App\Models\Course;

class GetCourseQuery
{
    /**
     * Retrieve detailed information about a specific course.
     *
     * @param string $courseSlug
     * @return Course
     */
    public function handle(string $courseSlug): Course
    {
        return Course::query()
            ->active()
            ->where('slug', $courseSlug)
            ->with(['author' => function ($query) {
                $query->with('roles')->withCount('courses');
            }])
            ->withCount(['lessons'])
            ->firstOrFail();
    }
}
