<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Auth;

use App\Actions\Auth\SendPasswordResetLinkAction;
use App\Data\Auth\Requests\SendPasswordResetLinkData;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class SendPasswordResetLinkController extends Controller
{
    #[OA\Post(
        path: '/auth/password/forgot',
        description: 'Send a password reset link to the user identified by the email.',
        summary: 'Send password reset email',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/SendPasswordResetLinkRequest')
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
            ),
        ]
    )]
    /**
     * Send a password reset link to the user identified by the email.
     *
     * @throws ValidationException
     */
    public function __invoke(SendPasswordResetLinkData $data, SendPasswordResetLinkAction $action): Response
    {
        $action->handle($data);

        return response()->noContent();
    }
}
