<?php

namespace App\Http\Controllers\Api\User;

use App\Actions\User\UnbanUserAction;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use OpenApi\Attributes as OA;
use Throwable;

class UnbanUserController extends Controller
{
    use AuthorizesRequests;

    #[OA\Patch(
        path: '/users/{user}/unban',
        description: 'Unban the specified user.',
        summary: 'Unban a user',
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
                description: 'User unbanned successfully.'
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
     * Unban the specified user.
     *
     * @param Request $request
     * @param User $user
     * @param UnbanUserAction $unbanUserAction
     * @return Response
     * @throws Throwable
     */
    public function __invoke(Request $request, User $user, UnbanUserAction $unbanUserAction): Response
    {
        $this->authorize('unban', $user);
        $unbanUserAction->handle($user);
        return response()->noContent();
    }
}
