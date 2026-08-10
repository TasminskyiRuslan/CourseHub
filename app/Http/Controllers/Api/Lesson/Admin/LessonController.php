<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Lesson\Admin;

use App\Actions\Lesson\DeleteLessonAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Lesson\Admin\LessonResource;
use App\Loaders\Lesson\Admin\LessonLoader;
use App\Models\Course;
use App\Models\Lesson;
use App\Queries\Lesson\Admin\GetLessonsQuery;
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
        path: '/admin/courses/{adminCourse}/lessons',
        description: 'Retrieve a paginated list of lessons for the specified course by administrator.',
        summary: '[Admin] Retrieve a list of course lessons',
        security: [['sanctum' => []]],
        tags: ['Lesson'],
        parameters: [
            new OA\Parameter(
                name: 'adminCourse',
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
                name: 'filter[trashed]',
                description: 'Filter by trashed state.',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string', enum: ['only', 'with', 'without'])
            ),
            new OA\Parameter(
                name: 'sort',
                description: 'Sort by field.',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'string',
                    enum: ['title', '-title', 'position', '-position', 'created_at', '-created_at', 'deleted_at', '-deleted_at']
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
                            items: new OA\Items(ref: '#/components/schemas/LessonAdminResponse')
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
     * Retrieve a paginated list of lessons for the specified course by administrator.
     */
    public function index(Request $request, GetLessonsQuery $query, Course $adminCourse): JsonResponse
    {
        $gottenLessons = $query->handle($request, $adminCourse);

        return LessonResource::collection($gottenLessons)
            ->response()
            ->setStatusCode(SymfonyResponse::HTTP_OK);
    }

    #[OA\Get(
        path: '/admin/courses/{adminCourse}/lessons/{adminLesson}',
        description: 'Retrieve detailed information about the specified lesson by administrator.',
        summary: '[Admin] Retrieve lesson details',
        security: [['sanctum' => []]],
        tags: ['Lesson'],
        parameters: [
            new OA\Parameter(
                name: 'adminCourse',
                description: 'Course identifier (slug).',
                in: 'path',
                required: true,
                schema: new OA\Schema(
                    type: 'string',
                    example: 'math-101'
                )
            ),
            new OA\Parameter(
                name: 'adminLesson',
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
                            ref: '#/components/schemas/LessonAdminResponse'
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
     * Retrieve detailed information about the specified lesson by administrator.
     */
    public function show(LessonLoader $lessonLoader, Course $adminCourse, Lesson $adminLesson): JsonResponse
    {
        $loadedLesson = $lessonLoader->handle($adminLesson);

        return LessonResource::make($loadedLesson)
            ->response()
            ->setStatusCode(SymfonyResponse::HTTP_OK);
    }

    #[OA\Delete(
        path: '/admin/courses/{adminCourse}/lessons/{adminLesson}',
        description: 'Delete the specified lesson by administrator.',
        summary: '[Admin] Delete a lesson',
        security: [['sanctum' => []]],
        tags: ['Lesson'],
        parameters: [
            new OA\Parameter(
                name: 'adminCourse',
                description: 'Course identifier (slug).',
                in: 'path',
                required: true,
                schema: new OA\Schema(
                    type: 'string',
                    example: 'math-101'
                )
            ),
            new OA\Parameter(
                name: 'adminLesson',
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
     * Delete the specified lesson by administrator.
     *
     * @throws Throwable
     */
    public function destroy(DeleteLessonAction $action, Course $adminCourse, Lesson $adminLesson): Response
    {
        $this->authorize('delete', $adminLesson);

        $action->handle($adminLesson);

        return response()->noContent();
    }
}
