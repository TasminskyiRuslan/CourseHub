<?php

namespace App\Http\Controllers\Api\Lesson;

use App\Data\Lesson\CreateLessonData;
use App\Data\Lesson\UpdateLessonData;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Lessons\LessonResource;
use App\Models\Course;
use App\Models\Lesson;
use App\Services\Lessons\LessonService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
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
        description: 'Retrieve a list of lessons for a specific course.',
        summary: 'List course lessons',
        security: [['sanctum' => []], []],
        tags: ['Lessons'],
        parameters: [
            new OA\Parameter(
                name: 'course',
                description: 'Course identifier (slug)',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'filter[search]',
                description: 'Search lessons by title',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'sort',
                description: 'Sort lessons by field. Use "-" prefix for descending order',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'string',
                    enum: ['title', '-title', 'position', '-position', 'created_at', '-created_at']
                )
            ),
            new OA\Parameter(
                name: 'page',
                description: 'Page number for pagination',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer', minimum: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: SymfonyResponse::HTTP_OK,
                description: 'Lessons list',
                content: new OA\JsonContent(ref: '#/components/schemas/LessonCollection')
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
    public function index(Course $course): AnonymousResourceCollection
    {
        $this->authorize('viewAny', [Lesson::class, $course]);
        $lessonsList = $this->lessonService->search($course);
        return LessonResource::collection($lessonsList);
    }

    #[OA\Post(
        path: '/courses/{course}/lessons',
        description: 'Create a new lesson for a specific course.',
        summary: 'Create lessons',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/StoreLessonRequest')
        ),
        tags: ['Lessons'],
        parameters: [
            new OA\Parameter(
                name: 'course',
                description: 'Course identifier (slug)',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string')
            ),
        ],
        responses: [
            new OA\Response(
                response: SymfonyResponse::HTTP_CREATED,
                description: 'Lesson created',
                content: new OA\JsonContent(ref: '#/components/schemas/Lesson')
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
    public function store(CreateLessonData $data, Course $course): LessonResource
    {
        $this->authorize('create', [Lesson::class, $course]);
        $newLesson = $this->lessonService->create($data, $course);
        return new LessonResource($newLesson);
    }

    #[OA\Get(
        path: '/courses/{course}/lessons/{lesson}',
        description: 'Retrieve a specific lesson for a specific course.',
        summary: 'Get lesson',
        security: [['sanctum' => []], []],
        tags: ['Lessons'],
        parameters: [
            new OA\Parameter(
                name: 'course',
                description: 'Course identifier (slug)',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'lesson',
                description: 'Lesson identifier (slug)',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string')
            )
        ],
        responses: [
            new OA\Response(
                response: SymfonyResponse::HTTP_OK,
                description: 'Lesson details',
                content: new OA\JsonContent(ref: '#/components/schemas/Lesson')
            ),
            new OA\Response(
                response: SymfonyResponse::HTTP_FORBIDDEN,
                description: 'Access denied'
            ),
            new OA\Response(
                response: SymfonyResponse::HTTP_NOT_FOUND,
                description: 'Course or Lesson not found'
            ),
        ]
    )]
    public function show(Course $course, Lesson $lesson): LessonResource
    {
        $this->authorize('view', $lesson);
        return new LessonResource($lesson->loadMissing('lessonable'));
    }

    #[OA\Put(
        path: '/courses/{course}/lessons/{lesson}',
        description: 'Update a specific lesson for a specific course.',
        summary: 'Update lesson',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/UpdateLessonRequest')
        ),
        tags: ['Lessons'],
        parameters: [
            new OA\Parameter(
                name: 'course',
                description: 'Course identifier (slug)',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'lesson',
                description: 'Lesson identifier (slug)',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string')
            )
        ],
        responses: [
            new OA\Response(
                response: SymfonyResponse::HTTP_OK,
                description: 'Lesson updated',
                content: new OA\JsonContent(ref: '#/components/schemas/Lesson')
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
                description: 'Course or Lesson not found'
            ),
        ]
    )]
    /**
     * @throws Throwable
     */
    public function update(UpdateLessonData $data, Course $course, Lesson $lesson): LessonResource
    {
        $this->authorize('update', $lesson);
        $updatedLesson = $this->lessonService->update($data, $lesson);
        return new LessonResource($updatedLesson);
    }

    #[OA\Delete(
        path: '/courses/{course}/lessons/{lesson}',
        description: 'Delete a specific lesson from a specific course.',
        summary: 'Delete lesson',
        security: [['sanctum' => []]],
        tags: ['Lessons'],
        parameters: [
            new OA\Parameter(
                name: 'course',
                description: 'Course identifier (slug)',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'lesson',
                description: 'Lesson identifier (slug)',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string')
            )
        ],
        responses: [
            new OA\Response(
                response: SymfonyResponse::HTTP_NO_CONTENT,
                description: 'Lesson deleted'
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
                description: 'Course or Lesson not found'
            ),
        ]
    )]
    /**
     * @throws Throwable
     */
    public function destroy(Course $course, Lesson $lesson): Response
    {
        $this->authorize('delete', $lesson);
        $this->lessonService->delete($lesson);
        return response()->noContent();
    }
}
