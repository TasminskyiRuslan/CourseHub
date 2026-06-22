<?php

namespace App\Http\Controllers\Api\User;

use App\Actions\User\UnbanUserAction;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UnbanUserController extends Controller
{
    use AuthorizesRequests;

    /**
     * Unban the specified user.
     *
     * @param Request $request
     * @param User $user
     * @param UnbanUserAction $unbanUserAction
     * @return Response
     */
    public function __invoke(Request $request, User $user, UnbanUserAction $unbanUserAction): Response
    {
        $this->authorize('unban', $user);
        $unbanUserAction->handle($user);
        return response()->noContent();
    }
}
