<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Lesson\Teacher;

use App\Actions\Lesson\CreateLessonAction;
use App\Actions\Lesson\DeleteLessonAction;
use App\Actions\Lesson\UpdateLessonAction;
use App\Data\Lesson\Requests\CreateLessonData;
use App\Data\Lesson\Requests\UpdateLessonData;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Lesson\Teacher\LessonResource;
use App\Loaders\Lesson\Teacher\LessonLoader;
use App\Models\Course;
use App\Models\Lesson;
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
        path: '/teacher/courses/{teacherCourse}/lessons',
        description: 'Retrieve a paginated list of lessons for the specified teacher\'s course.',
        summary: '[Teacher] Retrieve a list of course lessons',
        security: [['sanctum' => []]],
        tags: ['Lesson'],
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
     * Retrieve a paginated list of lessons for the specified teacher's course.
     */
    public function index(Request $request, GetLessonsQuery $query, Course $teacherCourse): JsonResponse
    {
        $gottenLessons = $query->handle($request, $teacherCourse);

        return LessonResource::collection($gottenLessons)
            ->response()
            ->setStatusCode(SymfonyResponse::HTTP_OK);
    }

    #[OA\Post(
        path: '/teacher/courses/{teacherCourse}/lessons',
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
                response: SymfonyResponse::HTTP_CREATED,
                description: 'Lesson created successfully.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/LessonTeacherResponse'
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
     * Create a new lesson for the specified teacher's course.
     *
     * @throws Throwable
     */
    public function store(CreateLessonData $data, CreateLessonAction $action, LessonLoader $lessonLoader, Course $teacherCourse): JsonResponse
    {
        $this->authorize('create', [Lesson::class, $teacherCourse]);

        $createdLesson = $action->handle($data, $teacherCourse);
        $loadedLesson = $lessonLoader->handle($createdLesson);

        return LessonResource::make($loadedLesson)
            ->response()
            ->setStatusCode(SymfonyResponse::HTTP_CREATED);
    }

    #[OA\Get(
        path: '/teacher/courses/{teacherCourse}/lessons/{teacherLesson}',
        description: 'Retrieve detailed information about the specified lesson for the specified teacher\'s course.',
        summary: '[Teacher] Retrieve lesson details',
        security: [['sanctum' => []]],
        tags: ['Lesson'],
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
            new OA\Parameter(
                name: 'teacherLesson',
                description: 'Lesson identifier (slug).',
                in: 'path',
                required: true,
                schema: new OA\Schema(
                    type: 'string',
                    example: 'introduction-to-algebra'
                )
            ),
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
                description: 'Course or lesson not found.'
            ),
        ]
    )]
    /**
     * Retrieve detailed information about the specified lesson for the specified teacher's course.
     */
    public function show(LessonLoader $lessonLoader, Course $teacherCourse, Lesson $teacherLesson): JsonResponse
    {
        $loadedLesson = $lessonLoader->handle($teacherLesson);

        return LessonResource::make($loadedLesson)
            ->response()
            ->setStatusCode(SymfonyResponse::HTTP_OK);
    }

    #[OA\Patch(
        path: '/teacher/courses/{teacherCourse}/lessons/{teacherLesson}',
        description: 'Update the specified lesson for the specified teacher\'s course.',
        summary: '[Teacher] Update a lesson',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/UpdateLessonRequest')
        ),
        tags: ['Lesson'],
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
            new OA\Parameter(
                name: 'teacherLesson',
                description: 'Lesson identifier (slug).',
                in: 'path',
                required: true,
                schema: new OA\Schema(
                    type: 'string',
                    example: 'introduction-to-algebra'
                )
            ),
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
                description: 'Course or Lesson not found.'
            ),
            new OA\Response(
                response: SymfonyResponse::HTTP_UNPROCESSABLE_ENTITY,
                description: 'Validation error.'
            ),
        ]
    )]
    /**
     * Update the specified lesson for the specified teacher's course.
     *
     * @throws Throwable
     */
    public function update(UpdateLessonData $data, UpdateLessonAction $action, LessonLoader $lessonLoader, Course $teacherCourse, Lesson $teacherLesson): JsonResponse
    {
        $this->authorize('update', $teacherLesson);

        $updatedLesson = $action->handle($data, $teacherLesson);
        $loadedLesson = $lessonLoader->handle($updatedLesson);

        return LessonResource::make($loadedLesson)
            ->response()
            ->setStatusCode(SymfonyResponse::HTTP_OK);
    }

    #[OA\Delete(
        path: '/teacher/courses/{teacherCourse}/lessons/{teacherLesson}',
        description: 'Delete the specified lesson for the specified teacher\'s course.',
        summary: '[Teacher] Delete a lesson',
        security: [['sanctum' => []]],
        tags: ['Lesson'],
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
            new OA\Parameter(
                name: 'teacherLesson',
                description: 'Lesson identifier (slug).',
                in: 'path',
                required: true,
                schema: new OA\Schema(
                    type: 'string',
                    example: 'introduction-to-algebra'
                )
            ),
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
     * Delete the specified lesson for the specified teacher's course.
     *
     * @throws Throwable
     */
    public function destroy(DeleteLessonAction $action, Course $teacherCourse, Lesson $teacherLesson): Response
    {
        $this->authorize('delete', $teacherLesson);

        $action->handle($teacherLesson);

        return response()->noContent();
    }
}
