<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Services\CourseService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class CoursePublishController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected CourseService $courseService,
    )
    {
    }

    public function publish(Course $course)
    {
        $this->authorize('update', $course);
        $this->courseService->publish($course);
        return response()->success(
            'Course published successfully.',
            $course->toResource()
        );
    }

    public function unpublish(Course $course)
    {
        $this->authorize('update', $course);
        $this->courseService->unpublish($course);
        return response()->success(
            'Course unpublished successfully.',
            $course->toResource()
        );
    }
}
