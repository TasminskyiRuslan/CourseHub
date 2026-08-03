<?php

namespace App\Loaders\Course\Admin;

use App\Models\Course;
use Illuminate\Contracts\Database\Eloquent\Builder;

readonly class LoadCourse
{
    /**
     * Eager load relations and counts for the specified course by administrator.
     *
     * @param Course $course
     * @return Course
     */
    public function handle(Course $course): Course
    {
        return $course->load([
            'author' => function (Builder $query) {
                $query->withTrashed()
                    ->with('roles')
                    ->withCount(['courses' => fn(Builder $q) => $q->withTrashed()]);
            }
        ])->loadCount([
            'lessons' => fn(Builder $query) => $query->withTrashed()
        ]);
    }
}
