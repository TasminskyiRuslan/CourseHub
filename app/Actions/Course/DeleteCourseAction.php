<?php

namespace App\Actions\Course;

use App\Models\Course;
use Illuminate\Support\Facades\Storage;
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
     */
    public function handle(Course $course): void
    {
        $imagePath = $course->image_path;
        $course->delete();
        if ($imagePath) {
            Storage::disk('courses')->delete($imagePath);
        }
    }
}
