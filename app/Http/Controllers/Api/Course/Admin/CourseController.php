<?php

namespace App\Http\Controllers\Api\Course\Admin;

use App\Actions\Course\DeleteCourseAction;
use App\Enums\CourseType;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Course\Admin\CourseResource;
use App\Models\Course;
use App\Queries\Course\Admin\GetCourseQuery;
use App\Queries\Course\Admin\GetCoursesQuery;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class CourseController extends Controller
{
    use AuthorizesRequests;

    #[OA\Get(
        path: '/admin/courses',
        description: 'Retrieve a paginated list of all courses by administrator.',
        summary: '[Admin] Retrieve a list of all courses',
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
                name: 'filter[author]',
                description: 'Filter courses by author slug.',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string'),
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
                name: 'filter[trashed]',
                description: 'Filter by trashed state.',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string', enum: ['with', 'only', 'without']),
            ),
            new OA\Parameter(
                name: 'sort',
                description: 'Sort courses by field.',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'string',
                    enum: ['title', '-title', 'price', '-price', 'created_at', '-created_at', 'published_at', '-published_at', 'banned_at', '-banned_at', 'deleted_at', '-deleted_at', 'lessons_count', '-lessons_count'],
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
                            items: new OA\Items(ref: '#/components/schemas/CourseAdminResponse')
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
     * Retrieve a paginated list of all courses by administrator.
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
        path: '/admin/courses/{course}',
        description: 'Retrieve detailed information about the specified course by administrator.',
        summary: '[Admin] Retrieve course details',
        security: [['sanctum' => []]],
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
                            ref: '#/components/schemas/CourseAdminResponse'
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
     * Retrieve detailed information about the specified course by administrator.
     *
     * @param GetCourseQuery $query
     * @param string $course
     * @return JsonResponse
     */
    public function show(GetCourseQuery $query, string $course): JsonResponse
    {
        $gottenCourse = $query->handle($course);

        return CourseResource::make($gottenCourse)
            ->response()
            ->setStatusCode(SymfonyResponse::HTTP_OK);
    }

    #[OA\Delete(
        path: '/admin/courses/{course}',
        description: 'Delete the specified course by administrator.',
        summary: '[Admin] Delete a course',
        security: [['sanctum' => []]],
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
     * Delete the specified course by administrator.
     *
     * @param DeleteCourseAction $action
     * @param Course $course
     * @return Response
     */
    public function destroy(DeleteCourseAction $action, Course $course): Response
    {
        $this->authorize('delete', $course);

        $action->handle($course);

        return response()->noContent();
    }
}
