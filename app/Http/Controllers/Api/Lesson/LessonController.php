<?php

namespace App\Http\Controllers\Api\Lesson;

use App\Actions\Lesson\CreateLessonAction;
use App\Actions\Lesson\UpdateLessonAction;
use App\Data\Lesson\CreateLessonData;
use App\Data\Lesson\UpdateLessonData;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Lesson\LessonResource;
use App\Models\Course;
use App\Models\Lesson;
use App\Queries\Lesson\GetLessonListQuery;
use App\Services\Lessons\LessonService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Throwable;

class LessonController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected LessonService $lessonService,
    )
    {
    }

    #[OA\Get(
        path: '/courses/{course}/lessons',
        description: 'Retrieve a paginated list of course lessons.',
        summary: 'Retrieve a list of course lessons',
        security: [['sanctum' => []], []],
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
                description: 'Sort lessons by field. Use "-" prefix for descending order.',
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
                schema: new OA\Schema(type: 'integer', minimum: 1)
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
                            items: new OA\Items(ref: '#/components/schemas/LessonResponse')
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
     * Retrieve a paginated list of course lessons.
     *
     * @param Course $course
     * @param GetLessonListQuery $getLessonListQuery
     * @return JsonResponse
     */
    public function index(Course $course, GetLessonListQuery $getLessonListQuery): JsonResponse
    {
        $this->authorize('view-any', [Lesson::class, $course]);
        $lessons = $getLessonListQuery->get($course, auth()->user());
        return LessonResource::collection($lessons)
            ->response()
            ->setStatusCode(SymfonyResponse::HTTP_OK);
    }

    #[OA\Post(
        path: '/courses/{course}/lessons',
        description: 'Create a new lesson.',
        summary: 'Create a lesson',
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
                            ref: '#/components/schemas/LessonResponse'
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
     * Create a new lesson.
     *
     * @param CreateLessonData $lessonData
     * @param Course $course
     * @param CreateLessonAction $createLessonAction
     * @return JsonResponse
     * @throws Throwable
     */
    public function store(CreateLessonData $lessonData, Course $course, CreateLessonAction $createLessonAction): JsonResponse
    {
        $this->authorize('create', [Lesson::class, $course]);
        $lesson = $createLessonAction->handle($lessonData, $course);
        return LessonResource::make($lesson)
            ->response()
            ->setStatusCode(SymfonyResponse::HTTP_CREATED);
    }

    #[OA\Get(
        path: '/courses/{course}/lessons/{lesson}',
        description: 'Retrieve detailed information about a specific lesson.',
        summary: 'Retrieve lesson details',
        security: [['sanctum' => []], []],
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
                            ref: '#/components/schemas/LessonResponse'
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
                description: 'Course or lesson not found.'
            ),
        ]
    )]
    /**
     * Retrieve detailed information about a specific lesson.
     *
     * @param Course $course
     * @param Lesson $lesson
     * @return JsonResponse
     */
    public function show(Course $course, Lesson $lesson): JsonResponse
    {
        $this->authorize('view', $lesson);
        return LessonResource::make($lesson->load(['lessonable']))
            ->response()
            ->setStatusCode(SymfonyResponse::HTTP_OK);
    }

    #[OA\Patch(
        path: '/courses/{course}/lessons/{lesson}',
        description: 'Update the specified lesson.',
        summary: 'Update a lesson',
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
                            ref: '#/components/schemas/LessonResponse'
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
     * Update the specified lesson.
     *
     * @param UpdateLessonData $lessonData
     * @param Course $course
     * @param Lesson $lesson
     * @param UpdateLessonAction $updateLessonAction
     * @return JsonResponse
     * @throws Throwable
     */
    public function update(UpdateLessonData $lessonData, Course $course, Lesson $lesson, UpdateLessonAction $updateLessonAction): JsonResponse
    {
        $this->authorize('update', $lesson);
        $lesson = $updateLessonAction->handle($lessonData, $lesson);
        return LessonResource::make($lesson)
            ->response()
            ->setStatusCode(SymfonyResponse::HTTP_OK);
    }

//    #[OA\Delete(
//        path: '/courses/{course}/lessons/{lesson}',
//        description: 'Delete a specific lesson from a specific course.',
//        summary: 'Delete lesson',
//        security: [['sanctum' => []]],
//        tags: ['Lessons'],
//        parameters: [
//            new OA\Parameter(
//                name: 'course',
//                description: 'Course identifier (slug)',
//                in: 'path',
//                required: true,
//                schema: new OA\Schema(type: 'string')
//            ),
//            new OA\Parameter(
//                name: 'lesson',
//                description: 'Lesson identifier (slug)',
//                in: 'path',
//                required: true,
//                schema: new OA\Schema(type: 'string')
//            )
//        ],
//        responses: [
//            new OA\Response(
//                response: SymfonyResponse::HTTP_NO_CONTENT,
//                description: 'Lesson deleted'
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
//                description: 'Course or Lesson not found'
//            ),
//        ]
//    )]
//    /**
//     * @throws Throwable
//     */
//    public function destroy(Course $course, Lesson $lesson): Response
//    {
//        $this->authorize('delete', $lesson);
//        $this->lessonService->delete($lesson);
//        return response()->noContent();
//    }
}
