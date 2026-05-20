<?php

namespace App\Http\Controllers\Api\User;

use App\Actions\User\UpdateUserRoleAction;
use App\Data\User\UpdateUserRoleData;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\User\UserResource;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class UserRoleController extends Controller
{
    use AuthorizesRequests;

    /**
     * Update the role of the specified user.
     *
     * @param UpdateUserRoleData $userRoleData
     * @param User $user
     * @param UpdateUserRoleAction $updateUserRoleAction
     * @return JsonResponse
     */
    public function update(UpdateUserRoleData $userRoleData, User $user, UpdateUserRoleAction $updateUserRoleAction): JsonResponse
    {
        $this->authorize('update-role', $user);
        $user = $updateUserRoleAction->handle($userRoleData, $user);
        return UserResource::make($user->load(['roles']))
            ->response()
            ->setStatusCode(SymfonyResponse::HTTP_OK);
    }
}
