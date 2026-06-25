<?php

namespace App\Http\Controllers\Api\Course;

use App\Actions\Course\BanCourseAction;
use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Throwable;

class BanCourseController extends Controller
{
    use AuthorizesRequests;

    /**
     * Ban the specified course.
     *
     * @param Request $request
     * @param Course $course
     * @param BanCourseAction $banCourseAction
     * @return Response
     * @throws Throwable
     */
    public function __invoke(Request $request, Course $course, BanCourseAction $banCourseAction): Response
    {
        $this->authorize('ban', $course);
        $banCourseAction->handle($course);
        return response()->noContent();
    }
}
