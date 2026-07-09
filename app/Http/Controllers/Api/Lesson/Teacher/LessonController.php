<?php

namespace App\Http\Controllers\Api\Lesson\Teacher;

use App\Actions\Lesson\CreateLessonAction;
use App\Actions\Lesson\DeleteLessonAction;
use App\Actions\Lesson\UpdateLessonAction;
use App\Data\Lesson\Requests\CreateLessonData;
use App\Data\Lesson\Requests\UpdateLessonData;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Lesson\Teacher\LessonResource;
use App\Models\Course;
use App\Models\Lesson;
use App\Queries\Lesson\Teacher\GetLessonQuery;
use App\Queries\Lesson\Teacher\GetLessonsQuery;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Throwable;

class LessonController extends Controller
{
    use AuthorizesRequests;

    #[OA\Get(
        path: '/teacher/courses/{course}/lessons',
        description: 'Retrieve a paginated list of lessons for the specified teacher\'s course.',
        summary: '[Teacher] Retrieve a list of course lessons',
        security: [['sanctum' => []]],
        tags: ['Lesson'],
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
            ),
            new OA\Parameter(
                name: 'filter[search]',
                description: 'Search lessons by title.',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'sort',
                description: 'Sort lessons by field.',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'string',
                    enum: ['title', '-title', 'position', '-position', 'created_at', '-created_at']
                )
            ),
            new OA\Parameter(
                name: 'page',
                description: 'Page number for pagination.',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer', default: 1, minimum: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: SymfonyResponse::HTTP_OK,
                description: 'Lesson list retrieved successfully.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/LessonTeacherResponse')
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
            )
        ]
    )]
    /**
     * Retrieve a paginated list of lessons for the specified teacher's course.
     *
     * @param Request $request
     * @param GetLessonsQuery $query
     * @param string $course
     * @return JsonResponse
     */
    public function index(Request $request, GetLessonsQuery $query, string $course): JsonResponse
    {
        $currentUser = $request->user();
        $gottenLessons = $query->handle($request, $course, $currentUser);
        return LessonResource::collection($gottenLessons)
            ->response()
            ->setStatusCode(SymfonyResponse::HTTP_OK);
    }

    #[OA\Post(
        path: '/teacher/courses/{course}/lessons',
        description: 'Create a new lesson for the specified teacher\'s course.',
        summary: '[Teacher] Create a lesson',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/CreateLessonRequest')
        ),
        tags: ['Lesson'],
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
                response: SymfonyResponse::HTTP_CREATED,
                description: 'Lesson created successfully.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/LessonTeacherResponse'
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
     * Create a new lesson for the specified teacher's course.
     *
     * @param CreateLessonData $data
     * @param CreateLessonAction $action
     * @param Course $course
     * @return JsonResponse
     * @throws Throwable
     */
    public function store(CreateLessonData $data, CreateLessonAction $action, Course $course): JsonResponse
    {
        $this->authorize('create', [Lesson::class, $course]);
        $createdLesson = $action->handle($data, $course);
        $createdLesson->loadMissing(['lessonable']);
        return LessonResource::make($createdLesson)
            ->response()
            ->setStatusCode(SymfonyResponse::HTTP_CREATED);
    }

    #[OA\Get(
        path: '/teacher/courses/{course}/lessons/{lesson}',
        description: 'Retrieve detailed information about the specified teacher\'s lesson.',
        summary: '[Teacher] Retrieve lesson details',
        security: [['sanctum' => []]],
        tags: ['Lesson'],
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
            ),
            new OA\Parameter(
                name: 'lesson',
                description: 'Lesson identifier (slug).',
                in: 'path',
                required: true,
                schema: new OA\Schema(
                    type: 'string',
                    example: 'introduction-to-algebra'
                )
            )
        ],
        responses: [
            new OA\Response(
                response: SymfonyResponse::HTTP_OK,
                description: 'Lesson details retrieved successfully.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/LessonTeacherResponse'
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
                description: 'Course or lesson not found.'
            )
        ]
    )]
    /**
     * Retrieve detailed information about the specified teacher's lesson.
     *
     * @param Request $request
     * @param GetLessonQuery $query
     * @param string $course
     * @param string $lesson
     * @return JsonResponse
     */
    public function show(Request $request, GetLessonQuery $query, string $course, string $lesson): JsonResponse
    {
        $currentUser = $request->user();
        $gottenLesson = $query->handle($course, $lesson, $currentUser);
        return LessonResource::make($gottenLesson)
            ->response()
            ->setStatusCode(SymfonyResponse::HTTP_OK);
    }

    #[OA\Patch(
        path: '/teacher/courses/{course}/lessons/{lesson}',
        description: 'Update the specified teacher\'s lesson.',
        summary: '[Teacher] Update a lesson',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/UpdateLessonRequest')
        ),
        tags: ['Lesson'],
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
            ),
            new OA\Parameter(
                name: 'lesson',
                description: 'Lesson identifier (slug).',
                in: 'path',
                required: true,
                schema: new OA\Schema(
                    type: 'string',
                    example: 'introduction-to-algebra'
                )
            )
        ],
        responses: [
            new OA\Response(
                response: SymfonyResponse::HTTP_OK,
                description: 'Lesson updated successfully.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/LessonTeacherResponse'
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
                description: 'Course or Lesson not found.'
            ),
            new OA\Response(
                response: SymfonyResponse::HTTP_UNPROCESSABLE_ENTITY,
                description: 'Validation error.'
            )
        ]
    )]
    /**
     * Update the specified teacher's lesson.
     *
     * @param UpdateLessonData $data
     * @param UpdateLessonAction $action
     * @param Course $course
     * @param Lesson $lesson
     * @return JsonResponse
     * @throws Throwable
     */
    public function update(UpdateLessonData $data, UpdateLessonAction $action, Course $course, Lesson $lesson): JsonResponse
    {
        $this->authorize('update', $lesson);
        $updatedLesson = $action->handle($data, $lesson);
        $updatedLesson->loadMissing(['lessonable']);
        return LessonResource::make($updatedLesson)
            ->response()
            ->setStatusCode(SymfonyResponse::HTTP_OK);
    }

    #[OA\Delete(
        path: '/teacher/courses/{course}/lessons/{lesson}',
        description: 'Delete the specified teacher\'s lesson.',
        summary: '[Teacher] Delete a lesson',
        security: [['sanctum' => []]],
        tags: ['Lesson'],
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
            ),
            new OA\Parameter(
                name: 'lesson',
                description: 'Lesson identifier (slug).',
                in: 'path',
                required: true,
                schema: new OA\Schema(
                    type: 'string',
                    example: 'introduction-to-algebra'
                )
            )
        ],
        responses: [
            new OA\Response(
                response: SymfonyResponse::HTTP_NO_CONTENT,
                description: 'Lesson deleted successfully.'
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
                description: 'Course or lesson not found.'
            ),
        ]
    )]
    /**
     * Delete the specified teacher's lesson.
     *
     * @param Course $course
     * @param Lesson $lesson
     * @param DeleteLessonAction $action
     * @return Response
     * @throws Throwable
     */
    public function destroy(DeleteLessonAction $action, Course $course, Lesson $lesson): Response
    {
        $this->authorize('delete', $lesson);
        $action->handle($lesson);
        return response()->noContent();
    }
}
