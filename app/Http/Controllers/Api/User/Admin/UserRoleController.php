<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\User\Admin;

use App\Actions\User\UpdateUserRoleAction;
use App\Data\User\Requests\UpdateUserRoleData;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\User\Admin\UserResource;
use App\Loaders\User\Admin\UserLoader;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Throwable;

class UserRoleController extends Controller
{
    use AuthorizesRequests;

    #[OA\Put(
        path: '/admin/users/{adminUser}/role',
        description: 'Update the role of the specified user by administrator.',
        summary: '[Admin] Update user role',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/UpdateUserRoleRequest')
        ),
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
                description: 'User role updated successfully.',
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
            new OA\Response(
                response: SymfonyResponse::HTTP_UNPROCESSABLE_ENTITY,
                description: 'Validation error.'
            ),
        ]
    )]
    /**
     * Update the role of the specified user by administrator.
     *
     * @throws Throwable
     */
    public function update(UpdateUserRoleData $data, UpdateUserRoleAction $action, UserLoader $userLoader, User $adminUser): JsonResponse
    {
        $this->authorize('update-roles', $adminUser);

        $updatedUser = $action->handle($data, $adminUser);
        $loadedUser = $userLoader->handle($updatedUser);

        return UserResource::make($loadedUser)
            ->response()
            ->setStatusCode(SymfonyResponse::HTTP_OK);
    }
}
