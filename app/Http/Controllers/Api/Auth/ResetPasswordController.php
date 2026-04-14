<?php

namespace App\Http\Controllers\Api\Auth;

use App\Actions\Auth\ResetPasswordAction;
use App\Data\Auth\Requests\ResetPasswordData;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class ResetPasswordController extends Controller
{
    use AuthorizesRequests;

    #[OA\Post(
        path: '/auth/password/reset',
        description: 'Reset the user\'s password.',
        summary: 'Reset password',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/ResetPasswordRequest')
        ),
        tags: ['Auth'],
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
