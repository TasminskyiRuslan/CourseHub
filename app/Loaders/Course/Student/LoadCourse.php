<?php

namespace App\Loaders\Course\Student;

use App\Models\Course;
use Illuminate\Contracts\Database\Eloquent\Builder;

readonly class LoadCourse
{
    /**
     * Eager load relations and counts for the specified student course.
     *
     * @param Course $course
     * @return Course
     */
    public function handle(Course $course): Course
    {
        return $course->load([
            'author' => function (Builder $query) {
                $query->with(['roles'])
                    ->withCount(['courses' => fn (Builder $q) => $q->active()]);
            },
        ])->loadCount([
            'lessons',
        ]);
    }
}
