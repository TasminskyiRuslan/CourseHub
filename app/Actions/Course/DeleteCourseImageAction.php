<?php

namespace App\Actions\Course;

use App\Models\Course;
use Illuminate\Support\Facades\Storage;

readonly class DeleteCourseImageAction
{
    /**
     * Delete the specified course image.
     *
     * @param Course $course
     * @return void
     */
    public function handle(Course $course): void
    {
        if (!$course->image_path) {
            return;
        }

        if (Storage::disk('courses')->exists($course->image_path)) {
            Storage::disk('courses')->delete($course->image_path);
        }

        $course->removeImage()->save();
    }
}
