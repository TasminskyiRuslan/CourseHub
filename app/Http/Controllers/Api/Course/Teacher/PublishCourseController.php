<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Course\Teacher;

use App\Actions\Course\PublishCourseAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Course\Teacher\CourseResource;
use App\Loaders\Course\Teacher\CourseLoader;
use App\Models\Course;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Throwable;

class PublishCourseController extends Controller
{
    use AuthorizesRequests;

    #[OA\Patch(
        path: '/teacher/courses/{teacherCourse}/publish',
        description: 'Publish the specified teacher\'s course.',
        summary: '[Teacher] Publish a course',
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
                description: 'Course published successfully.',
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
     * Publish the specified teacher's course.
     *
     * @throws Throwable
     */
    public function __invoke(PublishCourseAction $action, CourseLoader $courseLoader, Course $teacherCourse): JsonResponse
    {
        $this->authorize('publish', $teacherCourse);

        $publishedCourse = $action->handle($teacherCourse);
        $loadedCourse = $courseLoader->handle($publishedCourse);

        return CourseResource::make($loadedCourse)
            ->response()
            ->setStatusCode(SymfonyResponse::HTTP_OK);
    }
}
