<?php

namespace App\Http\Controllers\Api\Courses;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Services\Courses\CourseService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class UnpublishCourseController extends Controller
{
    use AuthorizesRequests;

    #[OA\Patch(
        path: '/courses/{course}/unpublish',
        description: 'Unpublish a specific course.',
        summary: 'Unpublish course',
        security: [['sanctum' => []]],
        tags: ['Courses'],
        parameters: [
            new OA\Parameter(
                name: 'course',
                description: 'Course identifier (slug)',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string')
            )
        ],
        responses: [
            new OA\Response(
                response: SymfonyResponse::HTTP_NO_CONTENT,
                description: 'Course unpublished'
            ),
            new OA\Response(
                response: SymfonyResponse::HTTP_UNAUTHORIZED,
                description: 'Authentication required'
            ),
            new OA\Response(
                response: SymfonyResponse::HTTP_FORBIDDEN,
                description: 'Access denied'
            ),
            new OA\Response(
                response: SymfonyResponse::HTTP_NOT_FOUND,
                description: 'Course not found'
            )
        ]
    )]
    public function __invoke(Request $request, Course $course, CourseService $service): Response
    {
        $this->authorize('update', $course);
        $service->unpublish($course);
        return response()->noContent();
    }
}
