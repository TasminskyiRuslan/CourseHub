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
     * @param string $courseSlug
     * @param string $lessonSlug
     * @param User $teacher
     * @return Lesson
     * @throws ModelNotFoundException
     */
    public function handle(string $courseSlug, string $lessonSlug, User $teacher): Lesson
    {
        return Lesson::query()
            ->where('slug', $lessonSlug)
            ->whereHas('course', function ($query) use ($teacher, $courseSlug) {
                $query->whereBelongsTo($teacher, 'author')
                    ->where('slug', $courseSlug);
            })
            ->with(['lessonable'])
            ->firstOrFail();
    }
}
