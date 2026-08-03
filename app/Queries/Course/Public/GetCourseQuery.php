<?php

namespace App\Queries\Course\Public;

use App\Models\Course;
use Illuminate\Database\Eloquent\ModelNotFoundException;

readonly class GetCourseQuery
{
    /**
     * Retrieve detailed information about the specified active course.
     *
     * @param string $courseSlug
     * @return Course
     * @throws ModelNotFoundException
     */
    public function handle(string $courseSlug): Course
    {
        return Course::query()
            ->active()
            ->where('slug', $courseSlug)
            ->with(['author' => function ($query) {
                $query->with(['roles'])
                    ->withCount(['courses' => fn ($q) => $q->active()]);
            }])
            ->withCount(['lessons'])
            ->firstOrFail();
    }
}
