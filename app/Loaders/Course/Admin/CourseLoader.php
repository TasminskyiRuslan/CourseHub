<?php

declare(strict_types=1);

namespace App\Loaders\Course\Admin;

use App\Models\Course;
use Illuminate\Contracts\Database\Eloquent\Builder;

readonly class CourseLoader
{
    /**
     * Eager load relations and counts for the specified course by administrator.
     */
    public function handle(Course $course): Course
    {
        return $course->load([
            'author' => function (Builder $query): void {
                $query->withTrashed()
                    ->with('roles')
                    ->withCount(['courses' => fn (Builder $q): Builder => $q->withTrashed()]);
            },
        ])->loadCount([
            'lessons' => fn (Builder $query): Builder => $query->withTrashed(),
        ]);
    }
}
