<?php

namespace App\Http\Controllers\Api\Auth;

use App\Actions\Auth\SendResetLinkAction;
use App\Data\Auth\ForgotPasswordData;
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
        description: 'Sends an email with a link to reset the password.',
        summary: 'Send password reset link',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/ForgotPasswordRequest')
        ),
        tags: ['Auth'],
        responses: [
            new OA\Response(
                response: SymfonyResponse::HTTP_NO_CONTENT,
                description: 'Reset link sent',
            ),
            new OA\Response(
                response: SymfonyResponse::HTTP_UNPROCESSABLE_ENTITY,
                description: 'Validation error'
            ),
        ]
    )]
    public function __invoke(ForgotPasswordData $data, SendResetLinkAction $action): Response
    {
        $action->handle($data->email);
        return response()->noContent();
    }
}
