<?php

namespace App\Queries\Lesson\Student;

use App\Models\Lesson;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

readonly class GetLessonQuery
{
    /**
     * Retrieve detailed information about the specified lesson for the specified student's enrolled course.
     *
     * @param User $student
     * @param string $courseSlug
     * @param string $lessonSlug
     * @return Lesson
     * @throws ModelNotFoundException
     */
    public function handle(User $student, string $courseSlug, string $lessonSlug): Lesson
    {
        $course = $student->enrolledCourses()
            ->active()
            ->where('slug', $courseSlug)
            ->firstOrFail();

        return $course->lessons()
            ->where('slug', $lessonSlug)
            ->with(['lessonable'])
            ->firstOrFail();
    }
}
