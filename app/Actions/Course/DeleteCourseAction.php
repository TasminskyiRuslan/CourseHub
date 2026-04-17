<?php

namespace App\Actions\Course;

use App\Models\Course;
use Illuminate\Support\Facades\DB;
use Throwable;

class DeleteCourseAction
{
    /**
     * @param DeleteCourseImageAction $deleteCourseImageAction
     */
    public function __construct(
        protected DeleteCourseImageAction $deleteCourseImageAction
    )
    {
    }

    /**
     * Remove the specified course and its image.
     *
     * @param Course $course
     * @return void
     * @throws Throwable
     */
    public function handle(Course $course): void
    {
        DB::transaction(function () use ($course) {
            $this->deleteCourseImageAction->handle($course);
            $course->delete();
        });
    }
}
