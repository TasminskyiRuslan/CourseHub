<?php

namespace App\Http\Controllers\Api\Auth;

use App\Actions\Auth\SendResetPasswordEmailAction;
use App\Data\Auth\Requests\ForgotPasswordData;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class ForgotPasswordController extends Controller
{
    use AuthorizesRequests;

    #[OA\Post(
        path: '/auth/password/forgot',
        description: 'Send an email with a link to reset the password.',
        summary: 'Send password reset link',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/ForgotPasswordRequest')
        ),
        tags: ['Auth'],
        responses: [
            new OA\Response(
                response: SymfonyResponse::HTTP_NO_CONTENT,
                description: 'Reset link sent successfully.',
            ),
            new OA\Response(
                response: SymfonyResponse::HTTP_UNPROCESSABLE_ENTITY,
                description: 'Validation error.'
            ),
            new OA\Response(
                response: SymfonyResponse::HTTP_TOO_MANY_REQUESTS,
                description: 'Too many requests.'
            )
        ]
    )]
    /**
     * Send an email with a link to reset the password.
     *
     * @param ForgotPasswordData $forgotPasswordData
     * @param SendResetPasswordEmailAction $sendResetPasswordEmailAction
     * @return Response
     */
    public function __invoke(ForgotPasswordData $forgotPasswordData, SendResetPasswordEmailAction $sendResetPasswordEmailAction): Response
    {
        $sendResetPasswordEmailAction->handle($forgotPasswordData);
        return response()->noContent();
    }
}
