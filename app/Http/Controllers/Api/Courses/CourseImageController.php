<?php

namespace App\Http\Controllers\Api\Courses;

use App\Data\Courses\UpdateCourseImageData;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Courses\CourseResource;
use App\Models\Course;
use App\Services\Courses\CourseService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
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

    #[OA\Put(
        path: '/courses/{course}/image',
        description: 'Update the image of a specific course.',
        summary: 'Update course image',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(ref: '#/components/schemas/UpdateCourseImageRequest')
            )
        ),
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
                response: SymfonyResponse::HTTP_OK,
                description: 'Image updated',
                content: new OA\JsonContent(ref: '#/components/schemas/Course')
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
                response: SymfonyResponse::HTTP_UNPROCESSABLE_ENTITY,
                description: 'Validation error'
            ),
            new OA\Response(
                response: SymfonyResponse::HTTP_NOT_FOUND,
                description: 'Course not found'
            )
        ]
    )]
    /**
     * @throws Throwable
     */
    public function update(UpdateCourseImageData $data, Course $course): CourseResource
    {
        $this->authorize('update', $course);
        $updatedCourse = $this->courseService->updateImage($course, $data->image);
        return new CourseResource($updatedCourse);
    }

    #[OA\Delete(
        path: '/courses/{course}/image',
        description: 'Delete the image of a specific course.',
        summary: 'Delete course image',
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
                description: 'Image deleted'
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
    /**
     * @throws Throwable
     */
    public function destroy(Course $course): Response
    {
        $this->authorize('update', $course);
        $this->courseService->deleteImage($course);
        return response()->noContent();
    }
}
