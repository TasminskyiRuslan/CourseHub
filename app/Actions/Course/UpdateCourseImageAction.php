<?php

namespace App\Actions\Course;

use App\Data\Course\Requests\UpdateCourseImageData;
use App\Models\Course;
use Exception;
use Illuminate\Support\Facades\Storage;
use Throwable;

class UpdateCourseImageAction
{
    /**
     * Update the specified course image.
     *
     * @param UpdateCourseImageData $courseImageData
     * @param Course $course
     * @return Course
     * @throws Exception
     */
    public function handle(UpdateCourseImageData $courseImageData, Course $course): Course
    {
        $oldPath = $course->image_path;
        $newPath = $courseImageData->image->store('/', 'courses');

        try {
            $course->setImage($newPath)->save();
        } catch (Exception $e) {
            Storage::disk('courses')->delete($newPath);
            throw $e;
        }

        if ($oldPath && Storage::disk('courses')->exists($oldPath)) {
            Storage::disk('courses')->delete($oldPath);
        }

        return $course;
    }
}
