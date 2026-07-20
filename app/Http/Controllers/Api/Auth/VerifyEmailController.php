<?php

namespace App\Http\Controllers\Api\Auth;

use App\Actions\Auth\VerifyEmailAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class VerifyEmailController extends Controller
{
    #[OA\Get(
        path: '/auth/email/verify/{id}/{hash}',
        description: 'Verify the email address of the user identified by the id.',
        summary: 'Verify email address',
        tags: ['Auth'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'Unique identifier of the user.',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            ),
            new OA\Parameter(
                name: 'hash',
                description: 'Hash of the email verification.',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'expires',
                description: 'Time of the link expiration.',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer')
            ),
            new OA\Parameter(
                name: 'signature',
                description: 'Signature of the email verification.',
                in: 'query',
                required: true,
                schema: new OA\Schema(type: 'string')
            )
        ],
        responses: [
            new OA\Response(
                response: SymfonyResponse::HTTP_NO_CONTENT,
                description: 'Email verified successfully.',
            ),
            new OA\Response(
                response: SymfonyResponse::HTTP_FORBIDDEN,
                description: 'Invalid or expired verification link.'
            ),
            new OA\Response(
                response: SymfonyResponse::HTTP_TOO_MANY_REQUESTS,
                description: 'Too many requests.'
            ),
        ]
    )]
    /**
     * Verify the email address of the user identified by the id.
     *
     * @param Request $request
     * @param VerifyEmailAction $action
     * @param string $id
     * @param string $hash
     * @return Response
     * @throws AccessDeniedHttpException
     */
    public function __invoke(Request $request, VerifyEmailAction $action, string $id, string $hash): Response
    {
        $action->handle($id, $hash);

        return response()->noContent();
    }
}
