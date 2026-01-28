<?php

namespace App\Http\Controllers\Api\Auth;

use App\Actions\Auth\RevokeCurrentTokenAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use OpenApi\Attributes as OA;

class LogoutController extends Controller
{
    #[OA\Post(
        path: '/auth/logout',
        description: 'Logout the current device by revoking the current access token.',
        summary: 'Logout current device',
        security: [['sanctum' => []]],
        tags: ['Auth'],
        responses: [
            new OA\Response(
                response: SymfonyResponse::HTTP_NO_CONTENT,
                description: 'User logged out'
            ),
            new OA\Response(
                response: SymfonyResponse::HTTP_UNAUTHORIZED,
                description: 'Unauthorized'
            )
        ]
    )]
    public function __invoke(Request $request, RevokeCurrentTokenAction $action): Response
    {
        $action->handle($request->user());
        return response()->noContent();
    }
}
