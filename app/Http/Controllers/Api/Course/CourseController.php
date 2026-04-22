<?php

namespace App\Http\Controllers\Api\Course;

use App\Actions\Course\CreateCourseAction;
use App\Actions\Course\DeleteCourseAction;
use App\Actions\Course\UpdateCourseAction;
use App\Data\Course\Requests\CreateCourseData;
use App\Data\Course\Requests\UpdateCourseData;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Courses\CourseResource;
use App\Models\Course;
use App\Queries\Course\GetCourseListQuery;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Throwable;

class CourseController extends Controller
{
    use AuthorizesRequests;

    #[OA\Get(
        path: '/courses',
        description: 'Retrieve a cached and paginated list of courses with filters and sorting.',
        summary: 'Retrieve a list of courses',
        security: [['sanctum' => []], []],
        tags: ['Course'],
        parameters: [
            new OA\Parameter(
                name: 'filter[search]',
                description: 'Search courses by title or description.',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string'),
            ),
            new OA\Parameter(
                name: 'sort',
                description: 'Sort courses by field. Use "-" prefix for descending order.',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'string',
                    enum: ['title', '-title', 'price', '-price', 'created_at', '-created_at']
                ),
            ),
            new OA\Parameter(
                name: 'page',
                description: 'Page number for pagination.',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer', minimum: 1),
            ),
            new OA\Parameter(
                name: 'include',
                description: 'Relations to include.',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'string',
                    example: 'author,lessons_count',
                )
            )
        ],
        responses: [
            new OA\Response(
                response: SymfonyResponse::HTTP_OK,
                description: 'Course list retrieved successfully.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/CourseResponse')
                        )
                    ]
                )
            )
        ]
    )]
    /**
     * Retrieve a cached and paginated list of courses with filters and sorting.
     *
     * @param GetCourseListQuery $getCourseListQuery
     * @return JsonResponse
     */
    public function index(GetCourseListQuery $getCourseListQuery): JsonResponse
    {
        $this->authorize('view-any', Course::class);
        $courses = $getCourseListQuery->get(auth()->user());
        return CourseResource::collection($courses)
            ->response()
            ->setStatusCode(SymfonyResponse::HTTP_OK);
    }

    #[OA\Post(
        path: '/courses',
        description: 'Create a new course.',
        summary: 'Create a course',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/CreateCourseRequest')
        ),
        tags: ['Course'],
        responses: [
            new OA\Response(
                response: SymfonyResponse::HTTP_CREATED,
                description: 'Course created successfully.',
                content: new OA\JsonContent(ref: '#/components/schemas/CourseResponse')
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
                response: SymfonyResponse::HTTP_UNPROCESSABLE_ENTITY,
                description: 'Validation error.'
            )
        ]
    )]
    /**
     * Create a new course.
     *
     * @param CreateCourseData $courseData
     * @param CreateCourseAction $createCourseAction
     * @return JsonResponse
     * @throws Throwable
     */
    public function store(CreateCourseData $courseData, CreateCourseAction $createCourseAction): JsonResponse
    {
        $this->authorize('create', Course::class);
        $course = $createCourseAction->handle($courseData, auth()->user());
        return CourseResource::make($course->loadCount('lessons')->loadMissing(['author']))
            ->response()
            ->setStatusCode(SymfonyResponse::HTTP_CREATED);
    }

    #[OA\Get(
        path: '/courses/{course}',
        description: 'Retrieve detailed information about a specific course.',
        summary: 'Retrieve course details',
        security: [['sanctum' => []], []],
        tags: ['Course'],
        parameters: [
            new OA\Parameter(
                name: 'course',
                description: 'Course identifier (slug).',
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
                description: 'Course details retrieved successfully.',
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
     * Retrieve detailed information about a specific course.
     *
     * @param Course $course
     * @return JsonResponse
     */
    public function show(Course $course): JsonResponse
    {
        $this->authorize('view', $course);
        return CourseResource::make($course->loadCount('lessons')->loadMissing(['author']))
            ->response()
            ->setStatusCode(SymfonyResponse::HTTP_OK);
    }

    #[OA\Patch(
        path: '/courses/{course}',
        description: 'Update the specified course.',
        summary: 'Update a course',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/UpdateCourseRequest')
        ),
        tags: ['Course'],
        parameters: [
            new OA\Parameter(
                name: 'course',
                description: 'Course identifier (slug).',
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
                description: 'Course updated successfully.',
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
            ),
        ]
    )]
    /**
     * Update the specified course.
     *
     * @param UpdateCourseData $courseData
     * @param Course $course
     * @param UpdateCourseAction $updateCourseAction
     * @return JsonResponse
     * @throws Throwable
     */
    public function update(UpdateCourseData $courseData, Course $course, UpdateCourseAction $updateCourseAction): JsonResponse
    {
        $this->authorize('update', $course);
        $course = $updateCourseAction->handle($courseData, $course);
        return CourseResource::make($course->loadCount('lessons')->loadMissing(['author']))
            ->response()
            ->setStatusCode(SymfonyResponse::HTTP_OK);
    }

    #[OA\Delete(
        path: '/courses/{course}',
        description: 'Remove the specified course.',
        summary: 'Remove a course',
        security: [['sanctum' => []]],
        tags: ['Course'],
        parameters: [
            new OA\Parameter(
                name: 'course',
                description: 'Course identifier (slug).',
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
                description: 'Course deleted successfully.'
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
     * Remove the specified course.
     *
     * @param Course $course
     * @param DeleteCourseAction $deleteCourseAction
     * @return Response
     * @throws Throwable
     */
    public function destroy(Course $course, DeleteCourseAction $deleteCourseAction): Response
    {
        $this->authorize('delete', $course);
        $deleteCourseAction->handle($course);
        return response()->noContent();
    }
}
