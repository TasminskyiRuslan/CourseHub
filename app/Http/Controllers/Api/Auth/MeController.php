<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Auth\UserResource;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class MeController extends Controller
{
    #[OA\Get(
        path: '/auth/me',
        description: 'Get the authenticated user\'s data.',
        summary: 'Get authenticated user',
        security: [['sanctum' => []]],
        tags: ['Auth'],
        responses: [
            new OA\Response(
                response: SymfonyResponse::HTTP_OK,
                description: 'Authenticated user data',
                content: new OA\JsonContent(ref: '#/components/schemas/User')
            ),
            new OA\Response(
                response: SymfonyResponse::HTTP_UNAUTHORIZED,
                description: 'Unauthorized'
            )
        ]
    )]
    public function __invoke(Request $request): UserResource
    {
        return new UserResource($request->user());
    }
}
