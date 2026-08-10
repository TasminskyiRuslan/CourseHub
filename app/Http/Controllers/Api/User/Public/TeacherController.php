<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\User\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\User\Public\TeacherResource;
use App\Loaders\User\Public\TeacherLoader;
use App\Models\User;
use App\Queries\User\Public\GetTeachersQuery;
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
            ),
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
                            items: new OA\Items(ref: '#/components/schemas/TeacherPublicResponse')
                        ),
                    ]
                )
            ),
        ]
    )]
    /**
     * Retrieve a paginated list of teachers.
     */
    public function index(Request $request, GetTeachersQuery $query): JsonResponse
    {
        $gottenTeachers = $query->handle($request);

        return TeacherResource::collection($gottenTeachers)
            ->response()
            ->setStatusCode(SymfonyResponse::HTTP_OK);
    }

    #[OA\Get(
        path: '/teachers/{publicTeacher}',
        description: 'Retrieve detailed information about the specified teacher.',
        summary: '[Public] Retrieve teacher details',
        tags: ['User'],
        parameters: [
            new OA\Parameter(
                name: 'publicTeacher',
                description: 'Teacher identifier (slug).',
                in: 'path',
                required: true,
                schema: new OA\Schema(
                    type: 'string',
                    example: 'john-doe'
                )
            ),
        ],
        responses: [
            new OA\Response(
                response: SymfonyResponse::HTTP_OK,
                description: 'Teacher details retrieved successfully.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/TeacherPublicResponse'
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: SymfonyResponse::HTTP_NOT_FOUND,
                description: 'Teacher not found.'
            ),
        ]
    )]
    /**
     * Retrieve detailed information about the specified teacher.
     */
    public function show(TeacherLoader $teacherLoader, User $publicTeacher): JsonResponse
    {
        $loadedTeacher = $teacherLoader->handle($publicTeacher);

        return TeacherResource::make($loadedTeacher)
            ->response()
            ->setStatusCode(SymfonyResponse::HTTP_OK);
    }
}
