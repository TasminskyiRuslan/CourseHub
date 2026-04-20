<?php

namespace App\Http\Controllers\Api\Course;

use App\Actions\Course\DeleteCourseImageAction;
use App\Actions\Course\UpdateCourseImageAction;
use App\Data\Course\Requests\UpdateCourseImageData;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Courses\CourseResource;
use App\Models\Course;
use App\Services\Courses\CourseService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Throwable;

class CourseImageController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected CourseService $courseService
    )
    {
    }

    #[OA\Post(
        path: '/courses/{course}/image',
        description: 'Update the specified course image.',
        summary: 'Update a course image',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(ref: '#/components/schemas/UpdateCourseImageRequest')
            )
        ),
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
                description: 'Course image updated successfully.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/CourseResponse'
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
            ),
            new OA\Response(
                response: SymfonyResponse::HTTP_UNPROCESSABLE_ENTITY,
                description: 'Validation error.'
            )
        ]
    )]
    /**
     * Update the specified course image.
     *
     * @param UpdateCourseImageData $courseImageData
     * @param Course $course
     * @param UpdateCourseImageAction $updateCourseImageAction
     * @return JsonResponse
     * @throws Throwable
     */
    public function update(UpdateCourseImageData $courseImageData, Course $course, UpdateCourseImageAction $updateCourseImageAction): JsonResponse
    {
        $this->authorize('update', $course);
        $course = $updateCourseImageAction->handle($courseImageData, $course);
        return CourseResource::make($course->loadCount('lessons')->loadMissing(['author', 'lessons']))
            ->response()
            ->setStatusCode(SymfonyResponse::HTTP_OK);
    }

    #[OA\Delete(
        path: '/courses/{course}/image',
        description: 'Remove the specified course image.',
        summary: 'Remove a course image',
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
                response: SymfonyResponse::HTTP_NO_CONTENT,
                description: 'Course image deleted successfully.'
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
            new OA\Response(
                response: SymfonyResponse::HTTP_UNPROCESSABLE_ENTITY,
                description: 'Validation error.'
            )
        ]
    )]
    /**
     * Remove the specified course image.
     *
     * @param Course $course
     * @param DeleteCourseImageAction $deleteCourseImageAction
     * @return Response
     */
    public function destroy(Course $course, DeleteCourseImageAction $deleteCourseImageAction): Response
    {
        $this->authorize('update', $course);
        $deleteCourseImageAction->handle($course);
        return response()->noContent();
    }
}
