<?php

namespace App\Http\Controllers\Api\Account;

use App\Actions\Account\LoginUserAction;
use App\Data\Account\Requests\LoginUserData;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Account\AccountResource;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class LoginController extends Controller
{
    #[OA\Post(
        path: '/account/login',
        description: 'Authenticate the user using email and password and issue an access token.',
        summary: 'Authenticate user',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/LoginUserRequest')
        ),
        tags: ['Account'],
        responses: [
            new OA\Response(
                response: SymfonyResponse::HTTP_OK,
                description: 'User authenticated successfully.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/AccountResponse'
                        )
                    ]
                )
            ),
            new OA\Response(
                response: SymfonyResponse::HTTP_UNPROCESSABLE_ENTITY,
                description: 'Invalid credentials or validation error.'
            ),
        ]
    )]
    /**
     * Authenticate the user using email and password and issue an access token.
     *
     * @param LoginUserData $userData
     * @param LoginUserAction $loginUserAction
     * @return JsonResponse
     */
    public function __invoke(LoginUserData $userData, LoginUserAction $loginUserAction): JsonResponse
    {
        $accountData = $loginUserAction->handle($userData);
        return AccountResource::make($accountData)
            ->response()
            ->setStatusCode(SymfonyResponse::HTTP_OK);
    }
}
