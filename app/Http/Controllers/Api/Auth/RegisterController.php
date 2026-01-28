<?php

namespace App\Http\Controllers\Api\Auth;

use App\Actions\Auth\RegisterUserAction;
use App\DTO\Auth\RegisterDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Auth\RegisterRequest;
use App\Http\Resources\Api\Auth\AuthResource;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Throwable;

class RegisterController extends Controller
{
    #[OA\Post(
        path: '/auth/register',
        description: 'Register a new user with name, email, password, and role.',
        summary: 'Register',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/RegisterRequest')
        ),
        tags: ['Auth'],
        responses: [
            new OA\Response(
                response: SymfonyResponse::HTTP_CREATED,
                description: 'User registered',
                content: new OA\JsonContent(ref: '#/components/schemas/Auth')
            ),
            new OA\Response(
                response: SymfonyResponse::HTTP_UNPROCESSABLE_ENTITY,
                description: 'Validation error'
            ),
        ]
    )]
    /**
     * @throws Throwable
     */
    public function __invoke(RegisterRequest $request, RegisterUserAction $action): AuthResource
    {
        $result = $action->handle(RegisterDTO::fromRequest($request));
        return new AuthResource($result);
    }
}
