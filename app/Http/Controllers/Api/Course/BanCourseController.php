<?php

namespace App\Http\Controllers\Api\Course;

use App\Actions\Course\BanCourseAction;
use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use OpenApi\Attributes as OA;
use Throwable;

class BanCourseController extends Controller
{
    use AuthorizesRequests;

    #[OA\Patch(
        path: '/courses/{course}/ban',
        description: 'Ban the specified course.',
        summary: 'Ban a course',
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
                description: 'Course banned successfully.'
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
