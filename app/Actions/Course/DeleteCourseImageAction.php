<?php

declare(strict_types=1);

namespace App\Actions\Course;

use App\Jobs\DeleteFileFromStorageJob;
use App\Models\Course;

readonly class DeleteCourseImageAction
{
    /**
     * Delete the image of the specified course.
     */
    public function handle(Course $course): void
    {
        $imagePath = $course->image_path;

        if (! $imagePath) {
            return;
        }

        $course->removeImage()->save();

        DeleteFileFromStorageJob::dispatch('courses', $imagePath);

    }
}
