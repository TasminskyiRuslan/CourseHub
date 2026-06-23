<?php

namespace App\Http\Controllers\Api\Auth;

use App\Actions\Auth\UpdateAccountImageAction;
use App\Data\Auth\Requests\UpdateAccountImageData;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\User\UserResource;
use Exception;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use OpenApi\Attributes as OA;

class AccountImageController extends Controller
{
    #[OA\Post(
        path: '/auth/account/image',
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
        tags: ['Auth'],
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
     * Update the specified user account image.
     *
     * @param UpdateAccountImageData $accountImageData
     * @param UpdateAccountImageAction $updateAccountImageAction
     * @return JsonResponse
     * @throws Exception
     */
    public function update(UpdateAccountImageData $accountImageData, UpdateAccountImageAction $updateAccountImageAction): JsonResponse
    {
        $account = $updateAccountImageAction->handle($accountImageData, auth()->user());
        return UserResource::make($account)
            ->response()
            ->setStatusCode(SymfonyResponse::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
