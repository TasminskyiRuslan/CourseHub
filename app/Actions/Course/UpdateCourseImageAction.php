<?php

namespace App\Actions\Course;

use App\Data\Course\Requests\UpdateCourseImageData;
use App\Models\Course;
use Exception;
use Illuminate\Support\Facades\DB;
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
     * @throws Throwable
     */
    public function handle(UpdateCourseImageData $courseImageData, Course $course): Course
    {
        return DB::transaction(function () use ($courseImageData, $course) {
            try {
                $oldPath = $course->image_path;
                $newPath = $courseImageData->image->store('/', 'courses');
                $course->setImage($newPath)->save();
                if ($oldPath) {
                    Storage::disk('courses')->delete($oldPath);
                }
                return $course;
            } catch (Exception $e) {
                if (isset($newPath)) {
                    Storage::disk('courses')->delete($newPath);
                }
                throw $e;
            }
        });
    }
}
