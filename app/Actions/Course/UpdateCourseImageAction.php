<?php

namespace App\Actions\Course;

use App\Data\Course\Requests\UpdateCourseImageData;
use App\Models\Course;
use Exception;
use Illuminate\Support\Facades\Storage;

readonly class UpdateCourseImageAction
{
    /**
     * Update the specified course image.
     *
     * @param UpdateCourseImageData $data
     * @param Course $course
     * @return Course
     * @throws Exception
     */
    public function handle(UpdateCourseImageData $data, Course $course): Course
    {
        $oldPath = $course->image_path;
        $newPath = $data->image->store('/', 'courses');

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
