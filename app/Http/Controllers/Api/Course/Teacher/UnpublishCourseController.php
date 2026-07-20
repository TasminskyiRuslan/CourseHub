<?php

namespace App\Http\Controllers\Api\Course\Teacher;

use App\Actions\Course\UnpublishCourseAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Course\Teacher\CourseResource;
use App\Models\Course;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class UnpublishCourseController extends Controller
{
    use AuthorizesRequests;

    #[OA\Patch(
        path: '/teacher/courses/{course}/unpublish',
        description: 'Unpublish the specified teacher\'s course.',
        summary: '[Teacher] Unpublish a course',
        security: [['sanctum' => []]],
        tags: ['Course'],
        parameters: [
            new OA\Parameter(
                name: 'course',
                description: 'Course identifier (slug)',
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
                response: SymfonyResponse::HTTP_OK,
                description: 'Course unpublished successfully.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/CourseTeacherResponse'
                        )
                    ]
                )
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
     * Unpublish the specified teacher's course.
     *
     * @param UnpublishCourseAction $action
     * @param Course $course
     * @return JsonResponse
     */
    public function __invoke(UnpublishCourseAction $action, Course $course): JsonResponse
    {
        $this->authorize('publish', $course);

        $unpublishedCourse = $action->handle($course);
        $unpublishedCourse->loadCount(['lessons']);

        return CourseResource::make($unpublishedCourse)
            ->response()
            ->setStatusCode(SymfonyResponse::HTTP_OK);
    }
}
