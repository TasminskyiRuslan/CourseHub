<?php

namespace App\Queries\Lesson\Teacher;

use App\Models\Lesson;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

readonly class GetLessonQuery
{
    /**
     * Retrieve detailed information about the specified lesson for the specified teacher's course.
     *
     * @param User $teacher
     * @param string $courseSlug
     * @param string $lessonSlug
     * @return Lesson
     * @throws ModelNotFoundException
     */
    public function handle(User $teacher, string $courseSlug, string $lessonSlug): Lesson
    {
        $course = $teacher->courses()
            ->where('slug', $courseSlug)
            ->firstOrFail();

        return $course->lessons()
            ->where('slug', $lessonSlug)
            ->with(['lessonable'])
            ->firstOrFail();
    }
}
