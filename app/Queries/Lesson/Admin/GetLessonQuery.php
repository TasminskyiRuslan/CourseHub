<?php

namespace App\Queries\Lesson\Admin;

use App\Models\Lesson;

class GetLessonQuery
{
    /**
     * Retrieve detailed information about the specified lesson for the specified course.
     *
     * @param string $courseSlug
     * @param string $lessonSlug
     * @return Lesson
     */
    public function handle(string $courseSlug, string $lessonSlug): Lesson
    {
        return Lesson::query()
            ->where('slug', $lessonSlug)
            ->whereHas('course', function ($query) use ($courseSlug) {
                $query->where('slug', $courseSlug)->withTrashed();
            })
            ->withTrashed()
            ->with(['lessonable' => fn($morphTo) => $morphTo->withTrashed()])
            ->firstOrFail();
    }
}
