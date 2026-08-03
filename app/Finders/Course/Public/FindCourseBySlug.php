<?php

namespace App\Finders\Course\Public;

use App\Models\Course;
use Illuminate\Database\Eloquent\ModelNotFoundException;

readonly class FindCourseBySlug
{
    /**
     * Find the specified active course by slug.
     *
     * @param string $slug
     * @return Course
     * @throws ModelNotFoundException
     */
    public function handle(string $slug): Course
    {
        return Course::query()
            ->active()
            ->where('slug', $slug)
            ->firstOrFail();
    }
}
