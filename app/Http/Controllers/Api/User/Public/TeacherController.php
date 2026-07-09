<?php

namespace App\Http\Controllers\Api\User\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\User\Public\UserResource;
use App\Models\User;
use App\Queries\User\Public\GetTeacherQuery;
use App\Queries\User\Public\GetTeachersQuery;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class TeacherController extends Controller
{
    #[OA\Get(
        path: '/teachers',
        description: 'Retrieve a paginated list of teachers.',
        summary: '[Public] Retrieve a list of teachers',
        tags: ['User'],
        parameters: [
            new OA\Parameter(
                name: 'filter[search]',
                description: 'Search users by name.',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string'),
            ),
            new OA\Parameter(
                name: 'sort',
                description: 'Sort teachers by field.',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'string',
                    enum: ['name', '-name', 'courses_count', '-courses_count', 'created_at', '-created_at']
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
                description: 'Teacher list retrieved successfully.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/UserPublicResponse')
                        )
                    ]
                )
            )
        ]
    )]
    /**
     * Retrieve a paginated list of teachers.
     *
     * @param Request $request
     * @param GetTeachersQuery $query
     * @return JsonResponse
     */
    public function index(Request $request, GetTeachersQuery $query): JsonResponse
    {
        $gottenUsers = $query->handle($request);
        return UserResource::collection($gottenUsers)
            ->response()
            ->setStatusCode(SymfonyResponse::HTTP_OK);
    }

    #[OA\Get(
        path: '/teachers/{teacher}',
        description: 'Retrieve detailed information about the specified teacher.',
        summary: '[Public] Retrieve teacher details',
        tags: ['User'],
        parameters: [
            new OA\Parameter(
                name: 'teacher',
                description: 'Teacher identifier (slug).',
                in: 'path',
                required: true,
                schema: new OA\Schema(
                    type: 'string',
                    example: 'john-doe'
                )
            )
        ],
        responses: [
            new OA\Response(
                response: SymfonyResponse::HTTP_OK,
                description: 'Teacher details retrieved successfully.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/UserPublicResponse'
                        )
                    ]
                )
            ),
            new OA\Response(
                response: SymfonyResponse::HTTP_NOT_FOUND,
                description: 'Teacher not found.'
            )
        ]
    )]
    /**
     * Retrieve detailed information about the specified teacher.
     *
     * @param GetTeacherQuery $query
     * @param string $teacher
     * @return JsonResponse
     */
    public function show(GetTeacherQuery $query, string $teacher): JsonResponse
    {
        $gottenUser = $query->handle($teacher);
        return UserResource::make($gottenUser)
            ->response()
            ->setStatusCode(SymfonyResponse::HTTP_OK);
    }
}
