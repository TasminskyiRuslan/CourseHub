<?php

namespace App\Queries\Lesson\Teacher;

use App\Models\Lesson;
use App\Models\User;

class GetLessonQuery
{
    /**
     * Retrieve detailed information about a specific lesson for teachers.
     *
     * @param string $courseSlug
     * @param string $lessonSlug
     * @param User $teacher
     * @return Lesson
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
