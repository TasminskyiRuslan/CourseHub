<?php

namespace App\Http\Controllers\Api\Auth;

use App\Actions\Auth\UpdateAccountAction;
use App\Data\Auth\Requests\UpdateAccountData;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\User\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use OpenApi\Attributes as OA;
use Throwable;

class AccountController extends Controller
{
    #[OA\Get(
        path: '/auth/account',
        description: 'Retrieve the current user account.',
        summary: 'Retrieve current user account',
        security: [['sanctum' => []]],
        tags: ['Auth'],
        responses: [
            new OA\Response(
                response: SymfonyResponse::HTTP_OK,
                description: 'User account retrieved successfully.',
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
                description: 'User account is unauthenticated.'
            )
        ]
    )]
    /**
     * Retrieve the current user account.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function show(Request $request): JsonResponse
    {
        return UserResource::make($request->user())
            ->response()
            ->setStatusCode(SymfonyResponse::HTTP_OK);
    }

    #[OA\Patch(
        path: '/auth/account',
        description: 'Update the current user account.',
        summary: 'Update current user account',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/UpdateAccountRequest')
        ),
        tags: ['Auth'],
        responses: [
            new OA\Response(
                response: SymfonyResponse::HTTP_OK,
                description: 'User account updated successfully.',
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
                description: 'User is unauthenticated.'
            ),
            new OA\Response(
                response: SymfonyResponse::HTTP_UNPROCESSABLE_ENTITY,
                description: 'Validation error.'
            ),
        ]
    )]
    /**
     * Update the specified user account.
     *
     * @param UpdateAccountData $accountData
     * @param Request $request
     * @param UpdateAccountAction $updateAccountAction
     * @return JsonResponse
     * @throws Throwable
     */
    public function update(UpdateAccountData $accountData, Request $request, UpdateAccountAction $updateAccountAction): JsonResponse
    {
        $user = $updateAccountAction->handle($accountData, $request->user());
        return UserResource::make($user)
            ->response()
            ->setStatusCode(SymfonyResponse::HTTP_OK);
    }
}
