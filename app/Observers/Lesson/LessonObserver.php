<?php

namespace App\Observers\Lesson;

use App\Models\Lesson;
use Illuminate\Support\Facades\Cache;

class LessonObserver
{
    /**
     * Set the default position for the lesson before creation.
     *
     * @param Lesson $lesson
     * @return void
     */
    public function creating(Lesson $lesson) : void
    {
        if (!is_null($lesson->position)) {
            return;
        }
        $maxPosition = Lesson::where('course_id', $lesson->course_id)->max('position');
        $lesson->position = $maxPosition + 1;
    }

    /**
     * Flush the associated course cache when a lesson is created.
     *
     * @param Lesson $lesson
     * @return void
     */
    public function created(Lesson $lesson): void
    {
        Cache::tags([config('cache.tags.course') . $lesson->course_id])->flush();
    }

    /**
     * Flush the associated course cache when a lesson is updated.
     *
     * @param Lesson $lesson
     * @return void
     */
    public function updated(Lesson $lesson): void
    {
        Cache::tags([config('cache.tags.course') . $lesson->course_id])->flush();
    }

    /**
     * Clean up associated polymorphic model before the lesson is removed.
     *
     * @param Lesson $lesson
     * @return void
     */
    public function deleting(Lesson $lesson): void
    {
        $lesson->lessonable?->delete();
    }

    /**
     * Flush the associated course cache when a lesson is deleted.
     *
     * @param Lesson $lesson
     * @return void
     */
    public function deleted(Lesson $lesson): void
    {
        Cache::tags([config('cache.tags.course') . $lesson->course_id])->flush();
    }
}
