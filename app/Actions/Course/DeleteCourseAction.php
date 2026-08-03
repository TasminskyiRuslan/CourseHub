<?php

namespace App\Actions\Course;

use App\Models\Course;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

readonly class DeleteCourseAction
{
    /**
     * Delete the specified course.
     *
     * @param Course $course
     * @return void
     * @throws Throwable
     */
    public function handle(Course $course): void
    {
        DB::transaction(function () use ($course) {
            if ($course->image_path) {
                Storage::disk('courses')->delete($course->image_path);
            }

            $course->delete();
        });
    }
}
