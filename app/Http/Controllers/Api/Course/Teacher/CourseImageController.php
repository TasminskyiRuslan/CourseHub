<?php

namespace App\Http\Controllers\Api\Course\Teacher;

use App\Actions\Course\DeleteCourseImageAction;
use App\Actions\Course\UpdateCourseImageAction;
use App\Data\Course\Requests\UpdateCourseImageData;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Course\Teacher\CourseResource;
use App\Models\Course;
use Exception;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class CourseImageController extends Controller
{
    use AuthorizesRequests;

    #[OA\Post(
        path: '/teacher/courses/{course}/image',
        description: 'Update the image of the specified teacher\'s course.',
        summary: '[Teacher] Update course image',
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
            ),
            new OA\Response(
                response: SymfonyResponse::HTTP_UNPROCESSABLE_ENTITY,
                description: 'Validation error.'
            )
        ]
    )]
    /**
     * Update the image of the specified teacher's course.
     *
     * @param UpdateCourseImageData $data
     * @param UpdateCourseImageAction $action
     * @param Course $course
     * @return JsonResponse
     * @throws Exception
     */
    public function update(UpdateCourseImageData $data, UpdateCourseImageAction $action, Course $course): JsonResponse
    {
        $this->authorize('update', $course);

        $updatedCourse = $action->handle($data, $course);
        $updatedCourse->loadCount(['lessons']);

        return CourseResource::make($updatedCourse)
            ->response()
            ->setStatusCode(SymfonyResponse::HTTP_OK);
    }

    #[OA\Delete(
        path: '/teacher/courses/{course}/image',
        description: 'Delete the image of the specified teacher\'s course.',
        summary: '[Teacher] Delete course image',
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
            )
        ]
    )]
    /**
     * Delete the image of the specified teacher's course.
     *
     * @param DeleteCourseImageAction $action
     * @param Course $course
     * @return Response
     */
    public function destroy(DeleteCourseImageAction $action, Course $course): Response
    {
        $this->authorize('update', $course);

        $action->handle($course);

        return response()->noContent();
    }
}
