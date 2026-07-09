<?php

namespace App\Http\Controllers\Api\User\Admin;

use App\Actions\User\UnbanUserAction;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Throwable;

class UnbanUserController extends Controller
{
    use AuthorizesRequests;

    #[OA\Patch(
        path: '/admin/users/{user}/unban',
        description: 'Unban the specified user by administrator.',
        summary: '[Admin] Unban a user',
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
     * Unban the specified user by administrator.
     *
     * @param UnbanUserAction $action
     * @param User $user
     * @return Response
     * @throws Throwable
     */
    public function __invoke(UnbanUserAction $action, User $user): Response
    {
        $this->authorize('ban', $user);
        $action->handle($user);
        return response()->noContent();
    }
}
