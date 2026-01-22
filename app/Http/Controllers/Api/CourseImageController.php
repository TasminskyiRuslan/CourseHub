<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ImageUploadRequest;
use App\Http\Resources\CourseResource;
use App\Models\Course;
use App\Services\CourseImageService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Storage;
use Throwable;

class CourseImageController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected CourseImageService $service
    )
    {
    }

    /**
     * @throws Throwable
     */
    public function store(ImageUploadRequest $request, Course $course)
    {
        $this->authorize('update', $course);
        $result = $this->service->upload($course, $request->file('image'));
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
        $this->service->delete($course);
        return response()->noContent();
    }
}
