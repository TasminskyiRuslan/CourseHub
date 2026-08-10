<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Lesson\Student;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Lesson\Student\LessonResource;
use App\Loaders\Lesson\Student\LessonLoader;
use App\Models\Course;
use App\Models\Lesson;
use App\Queries\Lesson\Student\GetLessonsQuery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class LessonController extends Controller
{
    #[OA\Get(
        path: '/student/courses/{studentCourse}/lessons',
        description: 'Retrieve a paginated list of lessons for the specified student\'s enrolled course.',
        summary: '[Student] Retrieve a list of course lessons',
        security: [['sanctum' => []]],
        tags: ['Lesson'],
        parameters: [
            new OA\Parameter(
                name: 'studentCourse',
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
                            items: new OA\Items(ref: '#/components/schemas/LessonStudentResponse')
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
     * Retrieve a paginated list of lessons for the specified student's enrolled course.
     */
    public function index(Request $request, GetLessonsQuery $query, Course $studentCourse): JsonResponse
    {
        $gottenLessons = $query->handle($request, $studentCourse);

        return LessonResource::collection($gottenLessons)
            ->response()
            ->setStatusCode(SymfonyResponse::HTTP_OK);
    }

    #[OA\Get(
        path: '/student/courses/{studentCourse}/lessons/{studentLesson}',
        description: 'Retrieve detailed information about the specified lesson for the specified student\'s enrolled course.',
        summary: '[Student] Retrieve lesson details',
        security: [['sanctum' => []]],
        tags: ['Lesson'],
        parameters: [
            new OA\Parameter(
                name: 'studentCourse',
                description: 'Course identifier (slug).',
                in: 'path',
                required: true,
                schema: new OA\Schema(
                    type: 'string',
                    example: 'math-101'
                )
            ),
            new OA\Parameter(
                name: 'studentLesson',
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
                            ref: '#/components/schemas/LessonStudentResponse'
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
     * Retrieve detailed information about the specified lesson for the specified student's enrolled course.
     */
    public function show(LessonLoader $lessonLoader, Course $studentCourse, Lesson $studentLesson): JsonResponse
    {
        $loadedLesson = $lessonLoader->handle($studentLesson);

        return LessonResource::make($loadedLesson)
            ->response()
            ->setStatusCode(SymfonyResponse::HTTP_OK);
    }
}
