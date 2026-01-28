<?php

namespace App\Http\Controllers\Api\Auth;

use App\Actions\Auth\RevokeAllTokensAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use OpenApi\Attributes as OA;

class LogoutAllController extends Controller
{
    #[OA\Delete(
        path: '/auth/tokens',
        description: 'Revoke all authentication tokens for the authenticated user.',
        summary: 'Logout from all devices',
        security: [['sanctum' => []]],
        tags: ['Auth'],
        responses: [
            new OA\Response(
                response: SymfonyResponse::HTTP_NO_CONTENT,
                description: 'All tokens revoked'
            ),
            new OA\Response(
                response: SymfonyResponse::HTTP_UNAUTHORIZED,
                description: 'Unauthorized'
            ),
        ]
    )]
    public function __invoke(Request $request, RevokeAllTokensAction $action): Response
    {
        $action->handle($request->user());
        return response()->noContent();
    }
}
