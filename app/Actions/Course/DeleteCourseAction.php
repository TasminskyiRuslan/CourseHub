<?php

namespace App\Actions\Course;

use App\Models\Course;
use Illuminate\Support\Facades\Storage;

class DeleteCourseAction
{
    /**
     * Delete the specified course and its image.
     *
     * @param Course $course
     * @return void
     */
    public function handle(Course $course): void
    {
        $imagePath = $course->image_path;
        $course->delete();
        if ($imagePath && Storage::disk('courses')->exists($imagePath)) {
            Storage::disk('courses')->delete($imagePath);
        }
    }
}
