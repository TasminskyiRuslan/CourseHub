<?php

namespace App\Finders\Course\Admin;

use App\Models\Course;
use Illuminate\Database\Eloquent\ModelNotFoundException;

readonly class FindCourseBySlug
{
    /**
     * Find the specified course by slug for administrator.
     *
     * @param string $slug
     * @return Course
     * @throws ModelNotFoundException
     */
    public function handle(string $slug): Course
    {
        return Course::query()
            ->withTrashed()
            ->where('slug', $slug)
            ->firstOrFail();
    }
}
