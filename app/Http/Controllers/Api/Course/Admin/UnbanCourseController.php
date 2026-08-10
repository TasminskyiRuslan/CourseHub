<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Course\Admin;

use App\Actions\Course\UnbanCourseAction;
use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Throwable;

class UnbanCourseController extends Controller
{
    use AuthorizesRequests;

    #[OA\Patch(
        path: '/admin/courses/{adminCourse}/unban',
        description: 'Unban the specified course by administrator.',
        summary: '[Admin] Unban a course',
        security: [['sanctum' => []]],
        tags: ['Course'],
        parameters: [
            new OA\Parameter(
                name: 'adminCourse',
                description: 'Course identifier (slug).',
                in: 'path',
                required: true,
                schema: new OA\Schema(
                    type: 'string',
                    example: 'math-101'
                )
            ),
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
            ),
        ]
    )]
    /**
     * Unban the specified course by administrator.
     *
     * @throws Throwable
     */
    public function __invoke(UnbanCourseAction $action, Course $adminCourse): Response
    {
        $this->authorize('ban', $adminCourse);

        $action->handle($adminCourse);

        return response()->noContent();
    }
}
