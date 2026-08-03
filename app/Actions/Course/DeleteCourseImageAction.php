<?php

namespace App\Actions\Course;

use App\Models\Course;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

readonly class DeleteCourseImageAction
{
    /**
     * Delete the image file and remove its reference from the specified course.
     *
     * @param Course $course
     * @return void
     * @throws Throwable
     */
    public function handle(Course $course): void
    {
        if (!$course->image_path) {
            return;
        }

        $imagePath = $course->image_path;

        DB::transaction(function () use ($course) {
            $course->removeImage()->save();
        });

        Storage::disk('courses')->delete($imagePath);
    }
}
