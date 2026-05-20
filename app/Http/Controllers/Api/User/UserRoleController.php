<?php

namespace App\Http\Controllers\Api\User;

use App\Actions\User\UpdateUserRoleAction;
use App\Data\User\UpdateUserRoleData;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\User\UserResource;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use OpenApi\Attributes as OA;

class UserRoleController extends Controller
{
    use AuthorizesRequests;

    #[OA\Put(
        path: '/users/{user}/role',
        description: 'Update the role of the specified user.',
        summary: 'Update user role',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/UpdateUserRoleRequest')
        ),
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
                description: 'User role updated successfully.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/UserResponse'
                        )
                    ]
                )
            ),
            new OA\Response(
                response: SymfonyResponse::HTTP_UNAUTHORIZED,
                description: 'User does not have permissions.'
            ),
            new OA\Response(
                response: SymfonyResponse::HTTP_NOT_FOUND,
                description: 'User not found.'
            ),
            new OA\Response(
                response: SymfonyResponse::HTTP_UNPROCESSABLE_ENTITY,
                description: 'Validation error.'
            )
        ]
    )]
    /**
     * Update the role of the specified user.
     *
     * @param UpdateUserRoleData $userRoleData
     * @param User $user
     * @param UpdateUserRoleAction $updateUserRoleAction
     * @return JsonResponse
     */
    public function update(UpdateUserRoleData $userRoleData, User $user, UpdateUserRoleAction $updateUserRoleAction): JsonResponse
    {
        $this->authorize('update-role', $user);
        $user = $updateUserRoleAction->handle($userRoleData, $user);
        return UserResource::make($user->load(['roles']))
            ->response()
            ->setStatusCode(SymfonyResponse::HTTP_OK);
    }
}
