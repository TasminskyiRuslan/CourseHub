<?php

namespace App\Http\Controllers\Api\Account;

use App\Actions\Account\ResetPasswordAction;
use App\Data\Account\Requests\ResetPasswordData;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class ResetPasswordController extends Controller
{
    use AuthorizesRequests;

    #[OA\Post(
        path: '/account/password/reset',
        description: 'Reset the user\'s password.',
        summary: 'Reset password',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/ResetPasswordRequest')
        ),
        tags: ['Account'],
        responses: [
            new OA\Response(
                response: SymfonyResponse::HTTP_NO_CONTENT,
                description: 'Password reset successfully.',
            ),
            new OA\Response(
                response: SymfonyResponse::HTTP_UNPROCESSABLE_ENTITY,
                description: 'Validation error.'
            ),
        ]
    )]
    /**
     * Reset the user's password.
     *
     * @param ResetPasswordData $resetPasswordData
     * @param ResetPasswordAction $resetPasswordAction
     * @return Response
     */
    public function __invoke(ResetPasswordData $resetPasswordData, ResetPasswordAction $resetPasswordAction): Response
    {
        $resetPasswordAction->handle($resetPasswordData);
        return response()->noContent();
    }
}
