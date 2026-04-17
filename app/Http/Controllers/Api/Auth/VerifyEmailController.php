<?php

namespace App\Http\Controllers\Api\Auth;

use App\Actions\Auth\VerifyEmailAction;
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
                description: 'Unique identifier of the user.',
                in: 'path',
                required: true,
                schema: new OA\Schema(
                    type: 'integer',
                    example: 1,
                )
            ),
            new OA\Parameter(
                name: 'hash',
                description: 'Hash of the email verification.',
                in: 'path',
                required: true,
                schema: new OA\Schema(
                    type: 'string',
                    example: '5224cb6fdd5bbe463af1db8ee499e858fcb79f81',
                )
            ),
            new OA\Parameter(
                name: 'expires',
                description: 'Time of the link expiration.',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'integer',
                    example: 1776361972,
                )
            ),
            new OA\Parameter(
                name: 'signature',
                description: 'Signature of the email verification.',
                in: 'query',
                required: true,
                schema: new OA\Schema(
                    type: 'string',
                    example: 'e697b7c8f76cae59e0121fbde183a47f7556ae973b5890d7ac7ee85a0a3e52bc',
                )
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
     * Verify the user's email address.
     *
     * @param Request $request
     * @param string $id
     * @param string $hash
     * @param VerifyEmailAction $verifyEmailAction
     * @return Response
     */
    public function __invoke(Request $request, string $id, string $hash, VerifyEmailAction $verifyEmailAction): Response
    {
        $verifyEmailAction->handle($id, $hash);
        return response()->noContent();
    }
}
