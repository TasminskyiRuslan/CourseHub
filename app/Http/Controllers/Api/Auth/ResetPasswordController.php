<?php

namespace App\Http\Controllers\Api\Auth;

use App\Actions\Auth\ResetPasswordAction;
use App\Data\Auth\Requests\ResetPasswordData;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class ResetPasswordController extends Controller
{
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
     * @param ResetPasswordData $data
     * @param ResetPasswordAction $action
     * @return Response
     * @throws ValidationException
     */
    public function __invoke(ResetPasswordData $data, ResetPasswordAction $action): Response
    {
        $action->handle($data);
        return response()->noContent();
    }
}
