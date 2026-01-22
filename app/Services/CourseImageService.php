<?php

namespace App\Services;

use App\Models\Course;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class CourseImageService
{
    /**
     * @throws Throwable
     */
    public function upload(Course $course, UploadedFile $image): Course
    {
        $oldImage = $course->image_path;
        $path = $image->store('courses', 'public');
        try {
            DB::transaction(function () use ($course, $path) {
                $course->update(['image_path' => $path]);
            });
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($path);
            throw $exception;
        }
        if ($oldImage) {
            Storage::disk('public')->delete($oldImage);
        }
        return $course;
    }

    /**
     * @throws Throwable
     */
    public function delete(Course $course): void
    {
        $image = $course->image_path;
        if(!$image) {
            return;
        }
        $course->update(['image_path' => null]);
        Storage::disk('public')->delete($image);
    }
}
