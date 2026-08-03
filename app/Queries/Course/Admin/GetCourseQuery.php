<?php

namespace App\Queries\Course\Admin;

use App\Models\Course;
use Illuminate\Database\Eloquent\ModelNotFoundException;

readonly class GetCourseQuery
{
    /**
     * Retrieve detailed information about the specified course by administrator.
     *
     * @param string $courseSlug
     * @return Course
     * @throws ModelNotFoundException
     */
    public function handle(string $courseSlug): Course
    {
        return Course::query()
            ->withTrashed()
            ->where('slug', $courseSlug)
            ->with(['author' => function ($query) {
                $query->withTrashed()
                    ->with('roles')
                    ->withCount(['courses' => fn($q) => $q->withTrashed()]);
            }])
            ->withCount(['lessons' => fn($query) => $query->withTrashed()])
            ->firstOrFail();
    }
}
