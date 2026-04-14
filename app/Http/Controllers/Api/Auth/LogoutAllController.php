<?php

namespace App\Http\Controllers\Api\Auth;

use App\Actions\Auth\RevokeAllTokensAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class LogoutAllController extends Controller
{
    #[OA\Delete(
        path: '/auth/tokens',
        description: 'Revoke all access tokens for an authenticated user.',
        summary: 'Logout from all devices',
        security: [['sanctum' => []]],
        tags: ['Auth'],
        responses: [
            new OA\Response(
                response: SymfonyResponse::HTTP_NO_CONTENT,
                description: 'User logged out successfully.'
            ),
            new OA\Response(
                response: SymfonyResponse::HTTP_UNAUTHORIZED,
                description: 'User is unauthenticated.'
            ),
        ]
    )]
    /**
     * Revoke all access tokens for an authenticated user.
     *
     * @param Request $request
     * @param RevokeAllTokensAction $revokeAllTokensAction
     * @return Response
     */
    public function __invoke(Request $request, RevokeAllTokensAction $revokeAllTokensAction): Response
    {
        $revokeAllTokensAction->handle($request->user());
        return response()->noContent();
    }
}
