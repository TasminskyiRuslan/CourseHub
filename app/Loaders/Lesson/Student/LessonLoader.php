<?php

declare(strict_types=1);

namespace App\Loaders\Lesson\Student;

use App\Models\Lesson;

readonly class LessonLoader
{
    /**
     * Eager load relations for the specified lesson for student.
     */
    public function handle(Lesson $lesson): Lesson
    {
        return $lesson->load(['lessonable']);
    }
}
