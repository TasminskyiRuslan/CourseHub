<?php

namespace App\Http\Controllers\Api\Auth;

use App\Actions\Auth\RegisterUserAction;
use App\Data\Auth\Requests\RegisterUserData;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Auth\AuthResource;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Throwable;

class RegisterController extends Controller
{
    #[OA\Post(
        path: '/auth/register',
        description: 'Register a new user and issue an access token.',
        summary: 'Register user',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/RegisterUserRequest')
        ),
        tags: ['Auth'],
        responses: [
            new OA\Response(
                response: SymfonyResponse::HTTP_CREATED,
                description: 'User registered successfully.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/AuthResponse'
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
        $authData = $registerUserAction->handle($userData);
        return AuthResource::make($authData)
            ->response()
            ->setStatusCode(SymfonyResponse::HTTP_CREATED);
    }
}
