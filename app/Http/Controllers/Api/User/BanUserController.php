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
