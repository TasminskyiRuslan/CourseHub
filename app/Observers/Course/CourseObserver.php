<?php

declare(strict_types=1);

namespace App\Observers\Course;

use App\Models\Course;
use Illuminate\Support\Facades\Cache;

class CourseObserver
{
    /**
     * Flush the cache when a new course is created.
     */
    public function created(Course $course): void
    {
        $this->flushCache($course);
    }

    /**
     * Flush the cache when a course is updated.
     */
    public function updated(Course $course): void
    {
        $this->flushCache($course);
    }

    /**
     * Remove associated lessons.php before the course is removed.
     */
    public function deleting(Course $course): void
    {
        $course->lessons()->get()->each->delete();
    }

    /**
     * Flush the cache when a course is deleted.
     */
    public function deleted(Course $course): void
    {
        $this->flushCache($course);
    }

    /**
     * Flush cache.
     */
    protected function flushCache(Course $course): void
    {
        Cache::tags([
            config('cache.tags.course_list'),
            config('cache.tags.teacher_list'),
        ])->flush();
    }
}
