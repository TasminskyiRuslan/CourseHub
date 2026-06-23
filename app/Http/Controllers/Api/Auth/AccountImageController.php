<?php

namespace App\Http\Controllers\Api\Auth;

use App\Actions\Auth\UpdateAccountImageAction;
use App\Data\Auth\Requests\UpdateAccountImageData;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\User\UserResource;
use Exception;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class AccountImageController extends Controller
{
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
