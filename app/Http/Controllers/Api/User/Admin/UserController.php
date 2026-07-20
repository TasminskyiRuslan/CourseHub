<?php

namespace App\Http\Controllers\Api\User\Admin;

use App\Actions\User\DeleteUserAction;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\User\Admin\UserResource;
use App\Models\User;
use App\Queries\User\Admin\GetUserQuery;
use App\Queries\User\Admin\GetUsersQuery;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

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
                schema: new OA\Schema(type: 'string', enum: [UserRole::TEACHER->value, UserRole::ADMIN->value, UserRole::SUPER_ADMIN->value]),
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
                    enum: ['only', 'with']
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
            )
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
                        )
                    ]
                )
            )
        ]
    )]
    /**
     * Retrieve a paginated list of users by administrator.
     *
     * @param Request $request
     * @param GetUsersQuery $query
     * @return JsonResponse
     */
    public function index(Request $request, GetUsersQuery $query): JsonResponse
    {
        $gottenUsers = $query->handle($request);

        return UserResource::collection($gottenUsers)
            ->response()
            ->setStatusCode(SymfonyResponse::HTTP_OK);
    }

    #[OA\Get(
        path: '/admin/users/{user}',
        description: 'Retrieve detailed information about the specified user by administrator.',
        summary: '[Admin] Retrieve user details',
        security: [['sanctum' => []]],
        tags: ['User'],
        parameters: [
            new OA\Parameter(
                name: 'user',
                description: 'User identifier (slug).',
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
                description: 'User details retrieved successfully.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/UserAdminResponse'
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
                description: 'User not found.'
            )
        ]
    )]
    /**
     * Retrieve detailed information about the specified user by administrator.
     *
     * @param GetUserQuery $query
     * @param string $user
     * @return JsonResponse
     */
    public function show(GetUserQuery $query, string $user): JsonResponse
    {
        $gottenUser = $query->handle($user);

        return UserResource::make($gottenUser)
            ->response()
            ->setStatusCode(SymfonyResponse::HTTP_OK);
    }

    #[OA\Delete(
        path: '/admin/users/{user}',
        description: 'Delete the specified user by administrator.',
        summary: '[Admin] Delete a user',
        security: [['sanctum' => []]],
        tags: ['User'],
        parameters: [
            new OA\Parameter(
                name: 'user',
                description: 'User identifier (slug).',
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
     *
     * @param DeleteUserAction $action
     * @param User $user
     * @return Response
     * @throws AccessDeniedHttpException
     */
    public function destroy(DeleteUserAction $action, User $user): Response
    {
        $this->authorize('delete', $user);

        $action->handle($user);

        return response()->noContent();
    }
}
