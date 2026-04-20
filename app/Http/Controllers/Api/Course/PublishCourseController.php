<?php

namespace App\Http\Controllers\Api\Course;

use App\Actions\Course\PublishCourseAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Courses\CourseResource;
use App\Models\Course;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class PublishCourseController extends Controller
{
    use AuthorizesRequests;

    #[OA\Patch(
        path: '/courses/{course}/publish',
        description: 'Publish the specified course.',
        summary: 'Publish a course',
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
                description: 'Course published successfully.'
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
     * Publish the specified course.
     *
     * @param Request $request
     * @param Course $course
     * @param PublishCourseAction $publishCourseAction
     * @return JsonResponse
     */
    public function __invoke(Request $request, Course $course, PublishCourseAction $publishCourseAction): JsonResponse
    {
        $this->authorize('publish', $course);
        $publishCourseAction->handle($course);
        return CourseResource::make($course->loadCount('lessons')->loadMissing(['author', 'lessons']))
            ->response()
            ->setStatusCode(SymfonyResponse::HTTP_OK);
    }
}
