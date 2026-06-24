<?php

namespace App\Http\Controllers\Api\Account;

use App\Actions\Account\UpdateAccountAction;
use App\Data\Account\Requests\UpdateAccountData;
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
        path: '/account',
        description: 'Retrieve the current user account.',
        summary: 'Retrieve current user account',
        security: [['sanctum' => []]],
        tags: ['Account'],
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
        path: '/account',
        description: 'Update the current user account.',
        summary: 'Update current user account',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/UpdateUserAccountRequest')
        ),
        tags: ['Account'],
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
     * @param UpdateAccountAction $updateAccountAction
     * @return JsonResponse
     */
    public function update(UpdateAccountData $accountData, UpdateAccountAction $updateAccountAction): JsonResponse
    {
        $account = $updateAccountAction->handle($accountData, auth()->user());
        return UserResource::make($account)
            ->response()
            ->setStatusCode(SymfonyResponse::HTTP_OK);
    }
}
