<?php

namespace App\Http\Controllers\Api\User;

use App\Actions\User\BanUserAction;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use OpenApi\Attributes as OA;

class BanUserController extends Controller
{
    use AuthorizesRequests;

    #[OA\Patch(
        path: '/users/{user}/ban',
        description: 'Ban the specified user.',
        summary: 'Ban a user',
        security: [['sanctum' => []]],
        tags: ['User'],
        parameters: [
            new OA\Parameter(
                name: 'user',
                description: 'User identifier (slug).',
                in: 'path',
                required: true,
                schema: new OA\Schema(
                    type: 'string',
                    example: 'john-doe'
                )
            )
        ],
        responses: [
            new OA\Response(
                response: SymfonyResponse::HTTP_NO_CONTENT,
                description: 'User banned successfully.'
            ),
            new OA\Response(
                response: SymfonyResponse::HTTP_UNAUTHORIZED,
                description: 'User is unauthenticated.'
            ),
            new OA\Response(
                response: SymfonyResponse::HTTP_FORBIDDEN,
                description: 'User does not have permissions.'
            ),
            new OA\Response(
                response: SymfonyResponse::HTTP_NOT_FOUND,
                description: 'User not found.'
            )
        ]
    )]
    /**
     * Ban the specified user.
     *
     * @param Request $request
     * @param User $user
     * @param BanUserAction $banUserAction
     * @return Response
     */
    public function __invoke(Request $request, User $user, BanUserAction $banUserAction): Response
    {
        $this->authorize('ban', $user);
        $banUserAction->handle($user);
        return response()->noContent();
    }
}
