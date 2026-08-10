<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Course\Teacher;

use App\Actions\Course\CreateCourseAction;
use App\Actions\Course\DeleteCourseAction;
use App\Actions\Course\UpdateCourseAction;
use App\Data\Course\Requests\CreateCourseData;
use App\Data\Course\Requests\UpdateCourseData;
use App\Enums\CourseType;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Course\Teacher\CourseResource;
use App\Loaders\Course\Teacher\CourseLoader;
use App\Models\Course;
use App\Queries\Course\Teacher\GetCoursesQuery;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Throwable;

class CourseController extends Controller
{
    use AuthorizesRequests;

    #[OA\Get(
        path: '/teacher/courses',
        description: 'Retrieve a paginated list of teacher\'s courses.',
        summary: '[Teacher] Retrieve a list of courses',
        security: [['sanctum' => []]],
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
                name: 'filter[type]',
                description: 'Filter courses by type.',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string', enum: [CourseType::OFFLINE->value, CourseType::ONLINE->value, CourseType::VIDEO->value]),
            ),
            new OA\Parameter(
                name: 'filter[banned]',
                description: 'Filter by banned status.',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'boolean', enum: ['true', 'false']),
            ),
            new OA\Parameter(
                name: 'filter[published]',
                description: 'Filter by publication status.',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'boolean', enum: ['true', 'false']),
            ),
            new OA\Parameter(
                name: 'sort',
                description: 'Sort courses by field.',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'string',
                    enum: ['title', '-title', 'price', '-price', 'created_at', '-created_at', 'published_at', '-published_at', 'lessons_count', '-lessons_count']
                ),
            ),
            new OA\Parameter(
                name: 'page',
                description: 'Page number for pagination.',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer', default: 1, minimum: 1),
            ),
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
                            items: new OA\Items(ref: '#/components/schemas/CourseTeacherResponse')
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
        ]
    )]
    /**
     * Retrieve a paginated list of teacher's courses.
     */
    public function index(Request $request, GetCoursesQuery $query): JsonResponse
    {
        $currentUser = $request->user();

        $gottenCourses = $query->handle($request, $currentUser);

        return CourseResource::collection($gottenCourses)
            ->response()
            ->setStatusCode(SymfonyResponse::HTTP_OK);
    }

    #[OA\Post(
        path: '/teacher/courses',
        description: 'Create a new teacher\'s course.',
        summary: '[Teacher] Create a course',
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
                response: SymfonyResponse::HTTP_UNPROCESSABLE_ENTITY,
                description: 'Validation error.'
            ),
        ]
    )]
    /**
     * Create a new teacher's course.
     *
     * @throws Throwable
     */
    public function store(Request $request, CreateCourseData $data, CreateCourseAction $action, CourseLoader $courseLoader): JsonResponse
    {
        $this->authorize('create', Course::class);

        $currentUser = $request->user();

        $createdCourse = $action->handle($data, $currentUser);
        $loadedCourse = $courseLoader->handle($createdCourse);

        return CourseResource::make($loadedCourse)
            ->response()
            ->setStatusCode(SymfonyResponse::HTTP_CREATED);
    }

    #[OA\Get(
        path: '/teacher/courses/{teacherCourse}',
        description: 'Retrieve detailed information about the specified teacher\'s course.',
        summary: '[Teacher] Retrieve course details',
        security: [['sanctum' => []]],
        tags: ['Course'],
        parameters: [
            new OA\Parameter(
                name: 'teacherCourse',
                description: 'Course identifier (slug).',
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
                description: 'Course details retrieved successfully.',
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
     * Retrieve detailed information about the specified teacher's course.
     */
    public function show(CourseLoader $courseLoader, Course $teacherCourse): JsonResponse
    {
        $loadedCourse = $courseLoader->handle($teacherCourse);

        return CourseResource::make($loadedCourse)
            ->response()
            ->setStatusCode(SymfonyResponse::HTTP_OK);
    }

    #[OA\Patch(
        path: '/teacher/courses/{teacherCourse}',
        description: 'Update the specified teacher\'s course.',
        summary: '[Teacher] Update a course',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/UpdateCourseRequest')
        ),
        tags: ['Course'],
        parameters: [
            new OA\Parameter(
                name: 'teacherCourse',
                description: 'Course identifier (slug).',
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
                description: 'Course updated successfully.',
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
     * Update the specified teacher's course.
     *
     * @throws Throwable
     */
    public function update(UpdateCourseData $data, UpdateCourseAction $action, CourseLoader $courseLoader, Course $teacherCourse): JsonResponse
    {
        $this->authorize('update', $teacherCourse);

        $updatedCourse = $action->handle($data, $teacherCourse);
        $loadedCourse = $courseLoader->handle($updatedCourse);

        return CourseResource::make($loadedCourse)
            ->response()
            ->setStatusCode(SymfonyResponse::HTTP_OK);
    }

    #[OA\Delete(
        path: '/teacher/courses/{teacherCourse}',
        description: 'Delete the specified teacher\'s course.',
        summary: '[Teacher] Delete a course',
        security: [['sanctum' => []]],
        tags: ['Course'],
        parameters: [
            new OA\Parameter(
                name: 'teacherCourse',
                description: 'Course identifier (slug).',
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
     * Delete the specified teacher's course.
     *
     * @throws Throwable
     */
    public function destroy(DeleteCourseAction $action, Course $teacherCourse): Response
    {
        $this->authorize('delete', $teacherCourse);

        $action->handle($teacherCourse);

        return response()->noContent();
    }
}
