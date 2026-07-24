<?php

namespace App\Http\Controllers\Api\Course\Student;

use App\Enums\CourseType;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Course\Student\CourseResource;
use App\Queries\Course\Student\GetCoursesQuery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class CourseController extends Controller
{
    #[OA\Get(
        path: '/student/courses',
        description: 'Retrieve a paginated list of student\'s enrolled courses.',
        summary: '[Student] Retrieve a list of enrolled courses',
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
                schema: new OA\Schema(
                    type: 'string',
                    enum: [CourseType::OFFLINE->value, CourseType::ONLINE->value, CourseType::VIDEO->value]
                ),
            ),
            new OA\Parameter(
                name: 'filter[author]',
                description: 'Filter courses by author slug.',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string'),
            ),
            new OA\Parameter(
                name: 'sort',
                description: 'Sort courses by field.',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'string',
                    enum: ['enrolled_at', '-enrolled_at', 'title', '-title', 'price', '-price', 'published_at', '-published_at', 'lessons_count', '-lessons_count']
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
                            items: new OA\Items(ref: '#/components/schemas/CourseStudentResponse')
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
            )
        ]
    )]
    /**
     * Retrieve a paginated list of student's courses.
     *
     * @param Request $request
     * @param GetCoursesQuery $query
     * @return JsonResponse
     */
    public function index(Request $request, GetCoursesQuery $query)
    {
        $currentUser = $request->user();

        $gottenCourses = $query->handle($request, $currentUser);

        return CourseResource::collection($gottenCourses)
            ->response()
            ->setStatusCode(SymfonyResponse::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
