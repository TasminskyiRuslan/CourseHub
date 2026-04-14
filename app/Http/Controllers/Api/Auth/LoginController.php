<?php

namespace App\Http\Controllers\Api\Auth;

use App\Actions\Auth\LoginUserAction;
use App\Data\Auth\Requests\LoginUserData;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Auth\AuthResource;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class LoginController extends Controller
{
    #[OA\Post(
        path: '/auth/login',
        description: 'Authenticate the user using email and password and issue an access token.',
        summary: 'Authenticate user',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/LoginUserRequest')
        ),
        tags: ['Auth'],
        responses: [
            new OA\Response(
                response: SymfonyResponse::HTTP_OK,
                description: 'User authenticated successfully.',
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
        $authData = $loginUserAction->handle($userData);
        return AuthResource::make($authData)
            ->response()
            ->setStatusCode(SymfonyResponse::HTTP_OK);
    }
}
