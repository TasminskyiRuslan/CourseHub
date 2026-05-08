<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\User\UserResource;
use App\Models\User;
use App\Queries\User\GetUserListQuery;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use OpenApi\Attributes as OA;

class UserController extends Controller
{
    use AuthorizesRequests;

    #[OA\Get(
        path: '/users',
        description: 'Retrieve a paginated list of users.',
        summary: 'Retrieve a list of users',
        security: [['sanctum' => []]],
        tags: ['User'],
        parameters: [
            new OA\Parameter(
                name: 'filter[search]',
                description: 'Search users by name or email.',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string'),
            ),
            new OA\Parameter(
                name: 'filter[role]',
                description: 'Filter users by role.',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string'),
            ),
            new OA\Parameter(
                name: 'filter[verified]',
                description: 'Filter users by email status.',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'string',
                    enum: ['true', 'false']
                ),
            ),
            new OA\Parameter(
                name: 'filter[banned]',
                description: 'Filter users by account status.',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'string',
                    enum: ['true', 'false']
                ),
            ),
            new OA\Parameter(
                name: 'filter[trashed]',
                description: 'Filter users by account existence.',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'string',
                    enum: ['only', 'with']
                ),
            ),
            new OA\Parameter(
                name: 'sort',
                description: 'Sort courses by field. Use "-" prefix for descending order.',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'string',
                    enum: ['name', '-name', 'email_verified_at', '-email_verified_at', 'banned_at', '-banned_at', 'created_at', '-created_at']
                ),
            ),
            new OA\Parameter(
                name: 'page',
                description: 'Page number for pagination.',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer', minimum: 1),
            )
        ],
        responses: [
            new OA\Response(
                response: SymfonyResponse::HTTP_OK,
                description: 'User list retrieved successfully.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/UserResponse')
                        )
                    ]
                )
            )
        ]
    )]
    /**
     * Retrieve a paginated list of users.
     *
     * @param GetUserListQuery $getUserListQuery
     * @return JsonResponse
     */
    public function index(GetUserListQuery $getUserListQuery): JsonResponse
    {
        $this->authorize('view-any', User::class);
        $users = $getUserListQuery->handle();
        return UserResource::collection($users)
            ->response()
            ->setStatusCode(SymfonyResponse::HTTP_OK);
    }

    /**
     * Retrieve detailed information about a specific user.
     *
     * @param User $user
     * @return JsonResponse
     */
    public function show(User $user): JsonResponse
    {
        $this->authorize('view', $user);
        return UserResource::make($user)
            ->response()
            ->setStatusCode(SymfonyResponse::HTTP_OK);
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
