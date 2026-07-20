<?php

namespace App\Http\Controllers\Api\User\Account;

use App\Actions\User\UpdateUserAction;
use App\Data\User\Requests\UpdateUserData;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\User\Account\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class AccountController extends Controller
{
    #[OA\Get(
        path: '/account',
        description: 'Retrieve the authenticated user\'s account.',
        summary: '[Account] Retrieve user account',
        security: [['sanctum' => []]],
        tags: ['User'],
        responses: [
            new OA\Response(
                response: SymfonyResponse::HTTP_OK,
                description: 'User account retrieved successfully.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/UserAccountResponse'
                        )
                    ]
                )
            ),
            new OA\Response(
                response: SymfonyResponse::HTTP_UNAUTHORIZED,
                description: 'User is unauthenticated.'
            )
        ]
    )]
    /**
     * Retrieve the authenticated user's account.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function show(Request $request): JsonResponse
    {
        $currentUser = $request->user();

        $currentUser->loadMissing(['roles']);

        return UserResource::make($currentUser)
            ->response()
            ->setStatusCode(SymfonyResponse::HTTP_OK);
    }

    #[OA\Patch(
        path: '/account',
        description: 'Update the authenticated user\'s account.',
        summary: '[Account] Update user account',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/UpdateUserAccountRequest')
        ),
        tags: ['User'],
        responses: [
            new OA\Response(
                response: SymfonyResponse::HTTP_OK,
                description: 'User account updated successfully.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/UserAccountResponse'
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
                response: SymfonyResponse::HTTP_UNPROCESSABLE_ENTITY,
                description: 'Validation error.'
            ),
        ]
    )]
    /**
     * Update the authenticated user's account.
     *
     * @param Request $request
     * @param UpdateUserData $data
     * @param UpdateUserAction $action
     * @return JsonResponse
     * @throws AccessDeniedHttpException
     */
    public function update(Request $request, UpdateUserData $data, UpdateUserAction $action): JsonResponse
    {
        $currentUser = $request->user();

        $updatedUser = $action->handle($data, $currentUser);
        $updatedUser->loadMissing(['roles']);

        return UserResource::make($updatedUser)
            ->response()
            ->setStatusCode(SymfonyResponse::HTTP_OK);
    }
}
