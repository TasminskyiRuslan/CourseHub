<?php

declare(strict_types=1);

namespace App\Observers\Lesson;

use App\Models\Lesson;
use Illuminate\Support\Facades\Cache;

class LessonObserver
{
    /**
     * Set the default position for the lesson before creation.
     */
    public function creating(Lesson $lesson): void
    {
        if (! is_null($lesson->position)) {
            return;
        }

        $maxPosition = Lesson::query()->where('course_id', $lesson->course_id)->max('position');

        $lesson->position = $maxPosition + 1;
    }

    /**
     * Flush the associated course cache when a lesson is created.
     */
    public function created(Lesson $lesson): void
    {
        $this->flushCache();
    }

    /**
     * Flush the associated course cache when a lesson is updated.
     */
    public function updated(Lesson $lesson): void
    {
        $this->flushCache();
    }

    /**
     * Clean up associated polymorphic model before the lesson is removed.
     */
    public function deleting(Lesson $lesson): void
    {
        $lesson->lessonable?->delete();
    }

    /**
     * Flush the associated course cache when a lesson is deleted.
     */
    public function deleted(Lesson $lesson): void
    {
        $this->flushCache();
    }

    /**
     * Flush cache.
     */
    protected function flushCache(): void
    {
        Cache::tags([
            config('cache.tags.course_list'),
        ])->flush();
    }
}
