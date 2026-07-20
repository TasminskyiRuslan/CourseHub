<?php

namespace App\Http\Controllers\Api\User\Account;

use App\Actions\User\DeleteUserAvatarAction;
use App\Actions\User\UpdateUserAvatarAction;
use App\Data\User\Requests\UpdateUserAvatarData;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\User\Account\UserResource;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class AccountAvatarController extends Controller
{
    #[OA\Post(
        path: '/account/avatar',
        description: 'Update the authenticated user\'s account avatar.',
        summary: '[Account] Update user avatar',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(ref: '#/components/schemas/UpdateUserAvatarRequest')
            )
        ),
        tags: ['User'],
        responses: [
            new OA\Response(
                response: SymfonyResponse::HTTP_OK,
                description: 'User account avatar updated successfully.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/UserAccountResponse'
                        )
                    ]
                )
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
                response: SymfonyResponse::HTTP_UNPROCESSABLE_ENTITY,
                description: 'Validation error.'
            )
        ]
    )]
    /**
     * Update the authenticated user\'s account avatar.
     *
     * @param Request $request
     * @param UpdateUserAvatarData $data
     * @param UpdateUserAvatarAction $action
     * @return JsonResponse
     * @throws Exception
     */
    public function update(Request $request, UpdateUserAvatarData $data, UpdateUserAvatarAction $action): JsonResponse
    {
        $currentUser = $request->user();

        $updatedUser = $action->handle($data, $currentUser);
        $updatedUser->loadMissing(['roles']);

        return UserResource::make($updatedUser)
            ->response()
            ->setStatusCode(SymfonyResponse::HTTP_OK);
    }

    #[OA\Delete(
        path: '/account/avatar',
        description: 'Delete the authenticated user\'s account avatar.',
        summary: '[Account] Remove user avatar',
        security: [['sanctum' => []]],
        tags: ['User'],
        responses: [
            new OA\Response(
                response: SymfonyResponse::HTTP_NO_CONTENT,
                description: 'User account avatar deleted successfully.'
            ),
            new OA\Response(
                response: SymfonyResponse::HTTP_UNAUTHORIZED,
                description: 'User is unauthenticated.'
            ),
            new OA\Response(
                response: SymfonyResponse::HTTP_FORBIDDEN,
                description: 'User does not have permissions.'
            )
        ]
    )]
    /**
     * Delete the authenticated user's account avatar.
     *
     * @param Request $request
     * @param DeleteUserAvatarAction $action
     * @return Response
     * @throws AccessDeniedHttpException
     */
    public function destroy(Request $request, DeleteUserAvatarAction $action): Response
    {
        $currentUser = $request->user();

        $action->handle($currentUser);

        return response()->noContent();
    }
}
