<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\User\Admin;

use App\Actions\User\BanUserAction;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Throwable;

class BanUserController extends Controller
{
    use AuthorizesRequests;

    #[OA\Patch(
        path: '/admin/users/{adminUser}/ban',
        description: 'Ban the specified user by administrator.',
        summary: '[Admin] Ban a user',
        security: [['sanctum' => []]],
        tags: ['User'],
        parameters: [
            new OA\Parameter(
                name: 'adminUser',
                description: 'User identifier (slug).',
                in: 'path',
                required: true,
                schema: new OA\Schema(
                    type: 'string',
                    example: 'john-doe'
                )
            ),
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
            ),
        ]
    )]
    /**
     * Ban the specified user by administrator.
     *
     * @throws AccessDeniedHttpException
     * @throws Throwable
     */
    public function __invoke(BanUserAction $banUserAction, User $adminUser): Response
    {
        $this->authorize('ban', $adminUser);

        $banUserAction->handle($adminUser);

        return response()->noContent();
    }
}
