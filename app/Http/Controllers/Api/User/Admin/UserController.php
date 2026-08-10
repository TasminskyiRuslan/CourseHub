<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\User\Admin;

use App\Actions\User\DeleteUserAction;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\User\Admin\UserResource;
use App\Loaders\User\Admin\UserLoader;
use App\Models\User;
use App\Queries\User\Admin\GetUsersQuery;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class UserController extends Controller
{
    use AuthorizesRequests;

    #[OA\Get(
        path: '/admin/users',
        description: 'Retrieve a paginated list of users by administrator.',
        summary: '[Admin] Retrieve a list of users',
        security: [['sanctum' => []]],
        tags: ['User'],
        parameters: [
            new OA\Parameter(
                name: 'filter[search]',
                description: 'Search by name or email.',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string'),
            ),
            new OA\Parameter(
                name: 'filter[role]',
                description: 'Filter by role.',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string', enum: ['without_role', UserRole::TEACHER->value, UserRole::ADMIN->value, UserRole::SUPER_ADMIN->value]),
            ),
            new OA\Parameter(
                name: 'filter[verified]',
                description: 'Filter by email status.',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'string',
                    enum: ['true', 'false']
                ),
            ),
            new OA\Parameter(
                name: 'filter[banned]',
                description: 'Filter by banned status.',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'string',
                    enum: ['true', 'false']
                ),
            ),
            new OA\Parameter(
                name: 'filter[trashed]',
                description: 'Filter by trashed state.',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'string',
                    enum: ['only', 'with', 'without']
                ),
            ),
            new OA\Parameter(
                name: 'sort',
                description: 'Sort courses by field.',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'string',
                    enum: ['name', '-name', 'email_verified_at', '-email_verified_at', 'courses_count', '-courses_count', 'banned_at', '-banned_at', 'created_at', '-created_at', 'deleted_at', '-deleted_at']
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
                response: SymfonyResponse::HTTP_UNAUTHORIZED,
                description: 'User is unauthenticated.'
            ),
            new OA\Response(
                response: SymfonyResponse::HTTP_FORBIDDEN,
                description: 'User does not have permissions.'
            ),
            new OA\Response(
                response: SymfonyResponse::HTTP_OK,
                description: 'User list retrieved successfully.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/UserAdminResponse')
                        ),
                    ]
                )
            ),
        ]
    )]
    /**
     * Retrieve a paginated list of users by administrator.
     */
    public function index(Request $request, GetUsersQuery $query): JsonResponse
    {
        $gottenUsers = $query->handle($request);

        return UserResource::collection($gottenUsers)
            ->response()
            ->setStatusCode(SymfonyResponse::HTTP_OK);
    }

    #[OA\Get(
        path: '/admin/users/{adminUser}',
        description: 'Retrieve detailed information about the specified user by administrator.',
        summary: '[Admin] Retrieve user details',
        security: [['sanctum' => []]],
        tags: ['User'],
        parameters: [
            new OA\Parameter(
                name: 'adminUser',
                description: 'User identifier (slug).',
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
                description: 'User details retrieved successfully.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/UserAdminResponse'
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
                description: 'User not found.'
            ),
        ]
    )]
    /**
     * Retrieve detailed information about the specified user by administrator.
     */
    public function show(UserLoader $userLoader, User $adminUser): JsonResponse
    {
        $gottenUser = $userLoader->handle($adminUser);

        return UserResource::make($gottenUser)
            ->response()
            ->setStatusCode(SymfonyResponse::HTTP_OK);
    }

    #[OA\Delete(
        path: '/admin/users/{adminUser}',
        description: 'Delete the specified user by administrator.',
        summary: '[Admin] Delete a user',
        security: [['sanctum' => []]],
        tags: ['User'],
        parameters: [
            new OA\Parameter(
                name: 'adminUser',
                description: 'User identifier (slug).',
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
                response: SymfonyResponse::HTTP_NO_CONTENT,
                description: 'User deleted successfully.'
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
                description: 'User not found.'
            ),
        ]
    )]
    /**
     * Delete the specified user by administrator.
     */
    public function destroy(DeleteUserAction $action, User $adminUser): Response
    {
        $this->authorize('delete', $adminUser);

        $action->handle($adminUser);

        return response()->noContent();
    }
}
