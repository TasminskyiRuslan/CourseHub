<?php

namespace App\Actions\Lesson;

use App\Data\Lesson\Requests\UpdateLessonData;
use App\Models\Lesson;
use Illuminate\Support\Facades\DB;
use Throwable;

readonly class UpdateLessonAction
{
    /**
     * Update the specified lesson.
     *
     * @param UpdateLessonData $data
     * @param Lesson $lesson
     * @return Lesson
     * @throws Throwable
     */
    public function handle(UpdateLessonData $data, Lesson $lesson): Lesson
    {
        return DB::transaction(function () use ($data, $lesson) {
            $lesson->update($data->all());
            $lesson->lessonable->update($data->all());

            return $lesson;
        });
    }
}
