<?php

namespace App\Http\Controllers\Api\Course;

use App\Actions\Course\UnbanCourseAction;
use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Throwable;

class UnbanCourseController extends Controller
{
    use AuthorizesRequests;

    #[OA\Patch(
        path: '/courses/{course}/unban',
        description: 'Unban the specified course.',
        summary: 'Unban a course',
        security: [['sanctum' => []]],
        tags: ['Course'],
        parameters: [
            new OA\Parameter(
                name: 'course',
                description: 'Course identifier (slug).',
                in: 'path',
                required: true,
                schema: new OA\Schema(
                    type: 'string',
                    example: 'math-101'
                )
            )
        ],
        responses: [
            new OA\Response(
                response: SymfonyResponse::HTTP_NO_CONTENT,
                description: 'Course unbanned successfully.'
            ),
            new OA\Response(
                response: SymfonyResponse::HTTP_UNAUTHORIZED,
                description: 'User is unauthenticated.'
            ),
            new OA\Response(
                response: SymfonyResponse::HTTP_FORBIDDEN,
                description: 'User does not have permissions.'
            ),
            new OA\Response(
                response: SymfonyResponse::HTTP_NOT_FOUND,
                description: 'Course not found.'
            )
        ]
    )]
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
