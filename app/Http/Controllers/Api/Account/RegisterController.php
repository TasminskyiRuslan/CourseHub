<?php

namespace App\Http\Controllers\Api\Account;

use App\Actions\Account\RegisterUserAction;
use App\Data\Account\Requests\RegisterUserData;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Account\AccountResource;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Throwable;

class RegisterController extends Controller
{
    #[OA\Post(
        path: '/account/register',
        description: 'Register a new user and issue an access token.',
        summary: 'Register a new user',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/RegisterUserRequest')
        ),
        tags: ['Account'],
        responses: [
            new OA\Response(
                response: SymfonyResponse::HTTP_CREATED,
                description: 'User registered successfully.',
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
                description: 'Validation error.'
            ),
        ]
    )]
    /**
     * Register a new user and issue an access token.
     *
     * @param RegisterUserData $userData
     * @param RegisterUserAction $registerUserAction
     * @return JsonResponse
     * @throws Throwable
     */
    public function __invoke(RegisterUserData $userData, RegisterUserAction $registerUserAction): JsonResponse
    {
        $accountData = $registerUserAction->handle($userData);
        return AccountResource::make($accountData)
            ->response()
            ->setStatusCode(SymfonyResponse::HTTP_CREATED);
    }
}
