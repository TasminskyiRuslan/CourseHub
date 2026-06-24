<?php

namespace App\Http\Controllers\Api\Account;

use App\Actions\Account\DeleteAccountAvatarAction;
use App\Actions\Account\UpdateAccountAvatarAction;
use App\Data\Account\Requests\UpdateAccountImageData;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\User\UserResource;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use OpenApi\Attributes as OA;

class AccountAvatarController extends Controller
{
    #[OA\Post(
        path: '/account/avatar',
        description: 'Update the current user account image.',
        summary: 'Update current user account image',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(ref: '#/components/schemas/UpdateUserAccountImageRequest')
            )
        ),
        tags: ['Account'],
        responses: [
            new OA\Response(
                response: SymfonyResponse::HTTP_OK,
                description: 'User account image updated successfully.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/UserResponse'
                        )
                    ]
                )
            ),
            new OA\Response(
                response: SymfonyResponse::HTTP_UNAUTHORIZED,
                description: 'User is unauthenticated.'
            ),
            new OA\Response(
                response: SymfonyResponse::HTTP_UNPROCESSABLE_ENTITY,
                description: 'Validation error.'
            )
        ]
    )]
    /**
     * Update the current user account image.
     *
     * @param UpdateAccountImageData $accountImageData
     * @param UpdateAccountAvatarAction $updateAccountImageAction
     * @return JsonResponse
     * @throws Exception
     */
    public function update(UpdateAccountImageData $accountImageData, UpdateAccountAvatarAction $updateAccountImageAction): JsonResponse
    {
        $account = $updateAccountImageAction->handle($accountImageData, auth()->user());
        return UserResource::make($account)
            ->response()
            ->setStatusCode(SymfonyResponse::HTTP_OK);
    }

    /**
     * Remove the current user account image.
     *
     * @param Request $request
     * @param DeleteAccountAvatarAction $deleteAccountAvatarAction
     * @return Response
     */
    public function destroy(Request $request, DeleteAccountAvatarAction $deleteAccountAvatarAction): Response
    {
        $deleteAccountAvatarAction->handle($request->user());
        return response()->noContent();
    }
}
