<?php

declare(strict_types=1);

namespace App\Actions\Course;

use App\Data\Course\Requests\UpdateCourseImageData;
use App\Jobs\DeleteFileFromStorageJob;
use App\Models\Course;
use Illuminate\Support\Facades\Storage;
use Throwable;

readonly class UpdateCourseImageAction
{
    /**
     * Update the specified course image and clean up the old file.
     *
     * @throws Throwable
     */
    public function handle(UpdateCourseImageData $data, Course $course): Course
    {
        $oldImagePath = $course->image_path;
        $newImagePath = $data->image->store('/', 'courses');

        try {
            $course->setImage($newImagePath)->save();

            if ($oldImagePath) {
                DeleteFileFromStorageJob::dispatch('courses', $oldImagePath);
            }
        } catch (Throwable $e) {
            Storage::disk('courses')->delete($newImagePath);

            throw $e;
        }

        return $course;
    }
}
