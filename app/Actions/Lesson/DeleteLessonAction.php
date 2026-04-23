<?php

namespace App\Actions\Lesson;

use App\Models\Lesson;
use Illuminate\Support\Facades\DB;
use Throwable;

class DeleteLessonAction
{
    /**
     * Remove the specified lesson.
     *
     * @param Lesson $lesson
     * @return void
     * @throws Throwable
     */
    public function handle(Lesson $lesson): void
    {
        DB::transaction(function () use ($lesson) {
            $lesson->lessonable()->delete();
            $lesson->delete();
        });
    }
}
