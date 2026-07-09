<?php

namespace App\Http\Controllers\Api\Auth;

use App\Actions\Auth\SendPasswordResetEmailAction;
use App\Data\Auth\Requests\SendPasswordResetEmailData;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class SendPasswordResetEmailController extends Controller
{
    #[OA\Post(
        path: '/auth/password/forgot',
        description: 'Send an email with a link to reset the password.',
        summary: 'Send password reset email',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/SendPasswordResetEmailRequest')
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
     * @param SendPasswordResetEmailData $data
     * @param SendPasswordResetEmailAction $action
     * @return Response
     * @throws ValidationException
     */
    public function __invoke(SendPasswordResetEmailData $data, SendPasswordResetEmailAction $action): Response
    {
        $action->handle($data);
        return response()->noContent();
    }
}
