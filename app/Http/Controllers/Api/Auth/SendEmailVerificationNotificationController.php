<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Auth;

use App\Actions\Auth\SendEmailVerificationNotificationAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class SendEmailVerificationNotificationController extends Controller
{
    #[OA\Post(
        path: '/auth/email/verification-notification',
        description: 'Send the email verification notification to the authenticated user.',
        summary: 'Send email verification notification',
        security: [['sanctum' => []]],
        tags: ['Auth'],
        responses: [
            new OA\Response(
                response: SymfonyResponse::HTTP_NO_CONTENT,
                description: 'Email verification notification sent successfully.',
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
            ),
        ]
    )]
    /**
     * Send the email verification notification to the authenticated user.
     *
     * @throws ValidationException
     */
    public function __invoke(Request $request, SendEmailVerificationNotificationAction $action): Response
    {
        $currentUser = $request->user();

        $action->handle($currentUser);

        return response()->noContent();
    }
}
