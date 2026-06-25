<?php

namespace App\Http\Controllers\Api\Course;

use App\Actions\Course\UnbanCourseAction;
use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Throwable;

class UnbanCourseController extends Controller
{
    use AuthorizesRequests;

    /**
     * Unban the specified course.
     *
     * @param Request $request
     * @param Course $course
     * @param UnbanCourseAction $unbanCourseAction
     * @return Response
     * @throws Throwable
     */
    public function __invoke(Request $request, Course $course, UnbanCourseAction $unbanCourseAction): Response
    {
        $this->authorize('unban', $course);
        $unbanCourseAction->handle($course);
        return response()->noContent();
    }
}
