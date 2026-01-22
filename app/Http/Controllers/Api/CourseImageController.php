<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ImageUploadRequest;
use App\Http\Resources\CourseResource;
use App\Models\Course;
use App\Services\CourseService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Throwable;

class CourseImageController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected CourseService $courseService
    )
    {
    }

    /**
     * @throws Throwable
     */
    public function store(ImageUploadRequest $request, Course $course)
    {
        $this->authorize('update', $course);
        $result = $this->courseService->uploadImage($course, $request->file('image'));
        return response()->success(
            'Image uploaded successfully.',
            new CourseResource($result),
        );
    }

    /**
     * @throws Throwable
     */
    public function destroy(Course $course)
    {
        $this->authorize('update', $course);
        $this->courseService->deleteImage($course);
        return response()->noContent();
    }
}
