<?php

namespace App\Http\Controllers\Api\Auth;

use App\Actions\Auth\ResendVerificationEmailAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class ResendVerificationEmailController extends Controller
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
                description: 'Verification email sent successfully.',
            ),
            new OA\Response(
                response: SymfonyResponse::HTTP_UNAUTHORIZED,
                description: 'User is unauthenticated.'
            ),
        ]
    )]
    /**
     * Resend the email verification notification to the authenticated user.
     *
     * @param Request $request
     * @param ResendVerificationEmailAction $resendVerificationEmailAction
     * @return Response
     */
    public function __invoke(Request $request, ResendVerificationEmailAction $resendVerificationEmailAction): Response
    {
        $resendVerificationEmailAction->handle($request->user());
        return response()->noContent();
    }
}
