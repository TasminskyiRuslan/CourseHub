<?php

namespace App\Actions\Course;

use App\Data\Course\Requests\UpdateCourseImageData;
use App\Models\Course;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

readonly class UpdateCourseImageAction
{
    /**
     * Update the specified course image and clean up the old file.
     *
     * @param UpdateCourseImageData $data
     * @param Course $course
     * @return Course
     * @throws Throwable
     */
    public function handle(UpdateCourseImageData $data, Course $course): Course
    {
        $oldPath = $course->image_path;
        $newPath = $data->image->store('/', 'courses');

        try {
            DB::transaction(function () use ($course, $newPath) {
                $course->setImage($newPath)->save();
            });
        } catch (Throwable $e) {
            Storage::disk('courses')->delete($newPath);
            throw $e;
        }

        if ($oldPath) {
            Storage::disk('courses')->delete($oldPath);
        }

        return $course;
    }
}
