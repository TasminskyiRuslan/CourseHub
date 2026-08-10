<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Course\Teacher;

use App\Actions\Course\DeleteCourseImageAction;
use App\Actions\Course\UpdateCourseImageAction;
use App\Data\Course\Requests\UpdateCourseImageData;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Course\Teacher\CourseResource;
use App\Loaders\Course\Teacher\CourseLoader;
use App\Models\Course;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Throwable;

class CourseImageController extends Controller
{
    use AuthorizesRequests;

    #[OA\Post(
        path: '/teacher/courses/{teacherCourse}/image',
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
                description: 'Course image updated successfully.',
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
            new OA\Response(
                response: SymfonyResponse::HTTP_UNPROCESSABLE_ENTITY,
                description: 'Validation error.'
            ),
        ]
    )]
    /**
     * Update the image of the specified teacher's course.
     *
     * @throws Throwable
     */
    public function update(UpdateCourseImageData $data, UpdateCourseImageAction $action, CourseLoader $courseLoader, Course $teacherCourse): JsonResponse
    {
        $this->authorize('update', $teacherCourse);

        $updatedCourse = $action->handle($data, $teacherCourse);
        $loadedCourse = $courseLoader->handle($updatedCourse);

        return CourseResource::make($loadedCourse)
            ->response()
            ->setStatusCode(SymfonyResponse::HTTP_OK);
    }

    #[OA\Delete(
        path: '/teacher/courses/{teacherCourse}/image',
        description: 'Delete the image of the specified teacher\'s course.',
        summary: '[Teacher] Delete course image',
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
        ]
    )]
    /**
     * Delete the image of the specified teacher's course.
     *
     * @throws Throwable
     */
    public function destroy(DeleteCourseImageAction $action, Course $teacherCourse): Response
    {
        $this->authorize('update', $teacherCourse);

        $action->handle($teacherCourse);

        return response()->noContent();
    }
}
