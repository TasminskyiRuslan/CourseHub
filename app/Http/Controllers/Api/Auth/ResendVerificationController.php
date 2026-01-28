<?php

namespace App\Http\Controllers\Api\Auth;

use App\Actions\Auth\ResendVerificationAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use OpenApi\Attributes as OA;

class ResendVerificationController extends Controller
{
    #[OA\Post(
        path: '/auth/email/verification-notification',
        description: 'Resend the email verification notification to the authenticated user.',
        summary: 'Resend verification email',
        security: [['sanctum' => []]],
        tags: ['Auth'],
        responses: [
            new OA\Response(
                response: SymfonyResponse::HTTP_NO_CONTENT,
                description: 'Verification email sent',
            ),
            new OA\Response(
                response: SymfonyResponse::HTTP_UNAUTHORIZED,
                description: 'Unauthorized'
            )
        ]
    )]
    public function __invoke(Request $request, ResendVerificationAction $action): Response
    {
        $action->handle($request->user());
        return response()->noContent();
    }
}
