<?php

namespace App\Actions\Lesson;

use App\Data\Lesson\UpdateLessonData;
use App\Models\Lesson;
use Illuminate\Support\Facades\DB;
use Throwable;

class UpdateLessonAction
{
    /**
     * Update the specified lesson.
     *
     * @param UpdateLessonData $lessonData
     * @param Lesson $lesson
     * @return Lesson
     * @throws Throwable
     */
    public function handle(UpdateLessonData $lessonData, Lesson $lesson): Lesson
    {
        return DB::transaction(function () use ($lessonData, $lesson) {
            $lesson->update($lessonData->all());
            $lesson->lessonable->update($lessonData->all());
            return $lesson->fresh(['lessonable']);
        });
    }
}
