<?php

declare(strict_types=1);

namespace App\Loaders\Course\Public;

use App\Models\Course;
use Illuminate\Contracts\Database\Eloquent\Builder;

readonly class CourseLoader
{
    /**
     * Eager load relations and counts for the specified active course.
     */
    public function handle(Course $course): Course
    {
        return $course->load([
            'author' => function (Builder $query): void {
                $query->with(['roles'])
                    ->withCount(['courses' => fn (Builder $q): Builder => $q->active()]);
            },
        ])->loadCount([
            'lessons',
        ]);
    }
}
