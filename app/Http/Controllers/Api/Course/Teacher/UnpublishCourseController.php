<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Course\Teacher;

use App\Actions\Course\UnpublishCourseAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Course\Teacher\CourseResource;
use App\Loaders\Course\Teacher\CourseLoader;
use App\Models\Course;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Throwable;

class UnpublishCourseController extends Controller
{
    use AuthorizesRequests;

    #[OA\Patch(
        path: '/teacher/courses/{teacherCourse}/unpublish',
        description: 'Unpublish the specified teacher\'s course.',
        summary: '[Teacher] Unpublish a course',
        security: [['sanctum' => []]],
        tags: ['Course'],
        parameters: [
            new OA\Parameter(
                name: 'teacherCourse',
                description: 'Course identifier (slug)',
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
                response: SymfonyResponse::HTTP_OK,
                description: 'Course unpublished successfully.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/CourseTeacherResponse'
                        ),
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
            ),
        ]
    )]
    /**
     * Unpublish the specified teacher's course.
     *
     * @throws Throwable
     */
    public function __invoke(UnpublishCourseAction $action, CourseLoader $courseLoader, Course $teacherCourse): JsonResponse
    {
        $this->authorize('publish', $teacherCourse);

        $unpublishedCourse = $action->handle($teacherCourse);
        $loadedCourse = $courseLoader->handle($unpublishedCourse);

        return CourseResource::make($loadedCourse)
            ->response()
            ->setStatusCode(SymfonyResponse::HTTP_OK);
    }
}
