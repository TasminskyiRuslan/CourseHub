<?php

namespace App\Http\Controllers\Api\Auth;

use App\Actions\Auth\VerifyEmailAction;
use App\Exceptions\Api\Auth\EmailVerificationFailedException;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class VerifyEmailController extends Controller
{
    #[OA\Get(
        path: '/auth/email/verify/{id}/{hash}',
        description: 'Verify the user\'s email address.',
        summary: 'Verify email address',
        tags: ['Auth'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'User ID',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'hash',
                description: 'Email verification hash',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'expires',
                description: 'Expiration timestamp',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer')
            ),
            new OA\Parameter(
                name: 'signature',
                description: 'Signed URL signature',
                in: 'query',
                required: true,
                schema: new OA\Schema(type: 'string')
            )
        ],
        responses: [
            new OA\Response(
                response: SymfonyResponse::HTTP_NO_CONTENT,
                description: 'Email verified successfully',
            ),
            new OA\Response(
                response: SymfonyResponse::HTTP_FORBIDDEN,
                description: 'Invalid or expired verification link'
            ),
        ]
    )]
    /**
     * @throws EmailVerificationFailedException
     */
    public function __invoke(Request $request, string $id, string $hash, VerifyEmailAction $action): Response
    {
        $action->handle($id, $hash);
        return response()->noContent();
    }
}
