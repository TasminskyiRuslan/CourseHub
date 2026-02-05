<?php

namespace App\Http\Controllers\Api\Courses;

use App\Data\Courses\CreateCourseData;
use App\Data\Courses\UpdateCourseData;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Courses\CourseResource;
use App\Models\Course;
use App\Services\Courses\CourseService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Throwable;

class CourseController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected CourseService $courseService,
    )
    {
    }

    #[OA\Get(
        path: '/courses',
        description: 'Retrieve a list of courses.',
        summary: 'List courses',
        tags: ['Courses'],
        parameters: [
            new OA\Parameter(
                name: 'filter[search]',
                description: 'Search courses by title or description',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string'),
            ),
            new OA\Parameter(
                name: 'sort',
                description: 'Sort courses by field. Use "-" prefix for descending order',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'string',
                    enum: ['title', '-title', 'price', '-price', 'created_at', '-created_at']
                ),
            ),
            new OA\Parameter(
                name: 'page',
                description: 'Page number for pagination',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer', minimum: 1),
            ),
        ],
        responses: [
            new OA\Response(
                response: SymfonyResponse::HTTP_OK,
                description: 'Courses list',
                content: new OA\JsonContent(ref: '#/components/schemas/CourseCollection')
            ),
        ]
    )]
    public function index(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Course::class);
        $coursesList = $this->courseService->search();
        return CourseResource::collection($coursesList);
    }

    #[OA\Post(
        path: '/courses',
        description: 'Create a new course.',
        summary: 'Create course',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/StoreCourseRequest')
        ),
        tags: ['Courses'],
        responses: [
            new OA\Response(
                response: SymfonyResponse::HTTP_CREATED,
                description: 'Course created',
                content: new OA\JsonContent(ref: '#/components/schemas/Course')
            ),
            new OA\Response(
                response: SymfonyResponse::HTTP_UNPROCESSABLE_ENTITY,
                description: 'Validation error'
            ),
            new OA\Response(
                response: SymfonyResponse::HTTP_UNAUTHORIZED,
                description: 'Authentication required'
            ),
            new OA\Response(
                response: SymfonyResponse::HTTP_FORBIDDEN,
                description: 'Access denied'
            ),
        ]
    )]
    /**
     * @throws Throwable
     */
    public function store(Request $request, CreateCourseData $data): CourseResource
    {
        $this->authorize('create', Course::class);
        $newCourse = $this->courseService->create($data, $request->user());
        return new CourseResource($newCourse);
    }

    #[OA\Get(
        path: '/courses/{course}',
        description: 'Retrieve a specific course.',
        summary: 'Get course',
        security: [['sanctum' => []], []],
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
                description: 'Course details',
                content: new OA\JsonContent(ref: '#/components/schemas/Course')
            ),
            new OA\Response(
                response: SymfonyResponse::HTTP_FORBIDDEN,
                description: 'Access denied'
            ),
            new OA\Response(
                response: SymfonyResponse::HTTP_NOT_FOUND,
                description: 'Course not found'
            ),
        ]
    )]
    public function show(Course $course): CourseResource
    {
        $this->authorize('view', $course);
        return new CourseResource($course->loadMissing('author'));
    }

    #[OA\Put(
        path: '/courses/{course}',
        description: 'Update a specific course.',
        summary: 'Update course',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/UpdateCourseRequest')
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
                description: 'Course updated',
                content: new OA\JsonContent(ref: '#/components/schemas/Course')
            ),
            new OA\Response(
                response: SymfonyResponse::HTTP_UNPROCESSABLE_ENTITY,
                description: 'Validation error'
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
            ),
        ]
    )]
    /**
     * @throws Throwable
     */
    public function update(UpdateCourseData $data, Course $course): CourseResource
    {
        $this->authorize('update', $course);
        $updatedCourse = $this->courseService->update($data, $course);
        return new CourseResource($updatedCourse);
    }

    #[OA\Delete(
        path: '/courses/{course}',
        description: 'Delete a specific course.',
        summary: 'Delete course',
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
                description: 'Course deleted'
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
            ),
        ]
    )]
    /**
     * @throws Throwable
     */
    public function destroy(Course $course): Response
    {
        $this->authorize('delete', $course);
        $this->courseService->delete($course);
        return response()->noContent();
    }
}
