<?php

namespace App\Http\Controllers\Api\Course\Public;

use App\Enums\CourseType;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Course\Public\CourseResource;
use App\Queries\Course\Public\GetCourseQuery;
use App\Queries\Course\Public\GetCoursesQuery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class CourseController extends Controller
{
    #[OA\Get(
        path: '/courses',
        description: 'Retrieve a paginated list of active courses.',
        summary: '[Public] Retrieve a list of courses',
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
                name: 'filter[author]',
                description: 'Filter courses by author slug.',
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
                name: 'sort',
                description: 'Sort courses by field.',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'string',
                    enum: ['title', '-title', 'price', '-price', 'published_at', '-published_at', 'lessons_count', '-lessons_count']
                ),
            ),
            new OA\Parameter(
                name: 'page',
                description: 'Page number for pagination.',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer', default: 1, minimum: 1),
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
                            items: new OA\Items(ref: '#/components/schemas/CoursePublicResponse')
                        )
                    ]
                )
            )
        ]
    )]
    /**
     * Retrieve a paginated list of active courses.
     *
     * @param Request $request
     * @param GetCoursesQuery $query
     * @return JsonResponse
     */
    public function index(Request $request, GetCoursesQuery $query): JsonResponse
    {
        $gottenCourses = $query->handle($request);
        return CourseResource::collection($gottenCourses)
            ->response()
            ->setStatusCode(SymfonyResponse::HTTP_OK);
    }

    #[OA\Get(
        path: '/courses/{course}',
        description: 'Retrieve detailed information about the specified active course.',
        summary: '[Public] Retrieve course details',
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
                            ref: '#/components/schemas/CoursePublicResponse'
                        )
                    ]
                )
            ),
            new OA\Response(
                response: SymfonyResponse::HTTP_NOT_FOUND,
                description: 'Course not found.'
            )
        ]
    )]
    /**
     * Retrieve detailed information about the specified active course.
     *
     * @param string $course
     * @param GetCourseQuery $query
     * @return JsonResponse
     */
    public function show(GetCourseQuery $query, string $course): JsonResponse
    {
        $gottenCourse = $query->handle($course);
        return CourseResource::make($gottenCourse)
            ->response()
            ->setStatusCode(SymfonyResponse::HTTP_OK);
    }
}
