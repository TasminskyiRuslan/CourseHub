<?php

namespace App\Queries\Course\Student;

use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

readonly class GetCourseQuery
{
    /**
     * Retrieve detailed information about the specified course for the specified student.
     *
     * @param string $courseSlug
     * @param User $student
     * @return Course
     * @throws ModelNotFoundException
     */
    public function handle(string $courseSlug, User $student): Course
    {
        return $student->enrolledCourses()
            ->active()
            ->where('slug', $courseSlug)
            ->with(['author' => function ($query) {
                $query->with(['roles'])->withCount(['courses' => fn ($q) => $q->active()]);
            }])
            ->withCount(['lessons'])
            ->firstOrFail();
    }
}
