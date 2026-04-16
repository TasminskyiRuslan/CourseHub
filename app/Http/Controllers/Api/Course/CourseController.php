<?php

namespace App\Http\Controllers\Api\Course;

use App\Actions\Course\CreateCourseAction;
use App\Data\Course\Requests\CreateCourseData;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Courses\CourseResource;
use App\Models\Course;
use App\Queries\Course\CourseListQuery;
use App\Services\Courses\CourseService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
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
        description: 'Retrieve a cached and paginated list of courses with filters and sorting.',
        summary: 'Retrieve a list of courses',
        security: [['sanctum' => []], []],
        tags: ['Course'],
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
            new OA\Parameter(
                name: 'include',
                description: 'Relations to include.',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'string',
                    example: 'author,lessons,lessons_count',
                ),
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
            ),
        ]
    )]
    /**
     * Retrieve a cached and paginated list of courses with filters and sorting.
     *
     * @param CourseListQuery $courseListQuery
     * @return JsonResponse
     */
    public function index(CourseListQuery $courseListQuery): JsonResponse
    {
        $this->authorize('view-any', Course::class);
        return CourseResource::collection($courseListQuery->handle())
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
            ),
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
        return CourseResource::make($course->loadCount('lessons')->loadMissing(['author', 'lessons']))
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
                description: 'Course identifier (slug)',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string')
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
                response: SymfonyResponse::HTTP_UNAUTHORIZED,
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
        return CourseResource::make($course->loadCount('lessons')->loadMissing(['author', 'lessons']))
            ->response()
            ->setStatusCode(SymfonyResponse::HTTP_OK);
    }

//    #[OA\Put(
//        path: '/courses/{course}',
//        description: 'Update a specific course.',
//        summary: 'Update course',
//        security: [['sanctum' => []]],
//        requestBody: new OA\RequestBody(
//            required: true,
//            content: new OA\JsonContent(ref: '#/components/schemas/UpdateCourseRequest')
//        ),
//        tags: ['Course'],
//        parameters: [
//            new OA\Parameter(
//                name: 'course',
//                description: 'Course identifier (slug)',
//                in: 'path',
//                required: true,
//                schema: new OA\Schema(type: 'string')
//            )
//        ],
//        responses: [
//            new OA\Response(
//                response: SymfonyResponse::HTTP_OK,
//                description: 'Course updated',
//                content: new OA\JsonContent(ref: '#/components/schemas/Course')
//            ),
//            new OA\Response(
//                response: SymfonyResponse::HTTP_UNPROCESSABLE_ENTITY,
//                description: 'Validation error'
//            ),
//            new OA\Response(
//                response: SymfonyResponse::HTTP_UNAUTHORIZED,
//                description: 'Authentication required'
//            ),
//            new OA\Response(
//                response: SymfonyResponse::HTTP_FORBIDDEN,
//                description: 'Access denied'
//            ),
//            new OA\Response(
//                response: SymfonyResponse::HTTP_NOT_FOUND,
//                description: 'Course not found'
//            ),
//        ]
//    )]
//    /**
//     * @throws Throwable
//     */
//    public function update(UpdateCourseData $data, Course $course): CourseResource
//    {
//        $this->authorize('update', $course);
//        $updatedCourse = $this->courseService->update($data, $course);
//        return new CourseResource($updatedCourse);
//    }
//
//    #[OA\Delete(
//        path: '/courses/{course}',
//        description: 'Delete a specific course.',
//        summary: 'Delete course',
//        security: [['sanctum' => []]],
//        tags: ['Course'],
//        parameters: [
//            new OA\Parameter(
//                name: 'course',
//                description: 'Course identifier (slug)',
//                in: 'path',
//                required: true,
//                schema: new OA\Schema(type: 'string')
//            )
//        ],
//        responses: [
//            new OA\Response(
//                response: SymfonyResponse::HTTP_NO_CONTENT,
//                description: 'Course deleted'
//            ),
//            new OA\Response(
//                response: SymfonyResponse::HTTP_UNAUTHORIZED,
//                description: 'Authentication required'
//            ),
//            new OA\Response(
//                response: SymfonyResponse::HTTP_FORBIDDEN,
//                description: 'Access denied'
//            ),
//            new OA\Response(
//                response: SymfonyResponse::HTTP_NOT_FOUND,
//                description: 'Course not found'
//            ),
//        ]
//    )]
//    /**
//     * @throws Throwable
//     */
//    public function destroy(Course $course): Response
//    {
//        $this->authorize('delete', $course);
//        $this->courseService->delete($course);
//        return response()->noContent();
//    }
}
