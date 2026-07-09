<?php

namespace App\Http\Middleware\Api\User;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class RestrictBannedUsers
{
    /**
     * Handle an incoming request to ensure the user is not banned.
     *
     * @param Request $request
     * @param Closure(Request): (SymfonyResponse) $next
     * @return SymfonyResponse
     */
    public function handle(Request $request, Closure $next): SymfonyResponse
    {
        $user = $request->user();
        if ($user && $user->isBanned()) {
            return response()->json([
                'message' => __('auth.banned'),
            ])->setStatusCode(SymfonyResponse::HTTP_FORBIDDEN);
        }
        return $next($request);
    }
}
