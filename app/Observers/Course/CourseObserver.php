<?php

namespace App\Observers\Course;

use App\Models\Course;
use Illuminate\Support\Facades\Cache;

class CourseObserver
{
    /**
     * Flush the cache when a new course is created.
     *
     * @param Course $course
     * @return void
     */
    public function created(Course $course): void
    {
        $this->flushCourseCache($course);
        Cache::tags([config('cache.tags.teacher_list')])->flush();
    }

    /**
     * Flush the cache when a course is updated.
     *
     * @param Course $course
     * @return void
     */
    public function updated(Course $course): void
    {
        $this->flushCourseCache($course);
    }

    /**
     * Remove associated lessons before the course is removed.
     *
     * @param Course $course
     * @return void
     */
    public function deleting(Course $course): void
    {
        $course->lessons()->get()->each->delete();
    }

    /**
     * Flush the cache when a course is deleted.
     *
     * @param Course $course
     * @return void
     */
    public function deleted(Course $course): void
    {
        $this->flushCourseCache($course);
        Cache::tags([config('cache.tags.teacher_list')])->flush();
    }

    /**
     * Flush course list and specific course cache.
     *
     * @param Course $course
     * @return void
     */
    protected function flushCourseCache(Course $course): void
    {
        Cache::tags([
            config('cache.tags.course_list'),
            config('cache.tags.course') . ':' . $course->id,
        ])->flush();
    }
}
