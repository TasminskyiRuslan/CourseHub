<?php

namespace App\Http\Controllers\Api\Auth;

use App\Actions\Auth\SendVerificationEmailAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class SendVerificationEmailController extends Controller
{
    #[OA\Post(
        path: '/auth/email/verification-notification',
        description: 'Send the email verification notification to the user.',
        summary: 'Send verification email',
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
            new OA\Response(
                response: SymfonyResponse::HTTP_UNPROCESSABLE_ENTITY,
                description: 'Email is already verified.'
            ),
             new OA\Response(
                response: SymfonyResponse::HTTP_TOO_MANY_REQUESTS,
                description: 'Too many requests.'
            )
        ]
    )]
    /**
     * Send the email verification notification to the user.
     *
     * @param Request $request
     * @param SendVerificationEmailAction $action
     * @return Response
     * @throws ValidationException
     */
    public function __invoke(Request $request, SendVerificationEmailAction $action): Response
    {
        $currentUser = $request->user();
        $action->handle($currentUser);
        return response()->noContent();
    }
}
