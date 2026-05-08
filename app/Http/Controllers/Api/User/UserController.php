<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\User\UserResource;
use App\Models\User;
use App\Queries\User\GetUserListQuery;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class UserController extends Controller
{
    use AuthorizesRequests;

    /**
     * Retrieve a paginated list of users.
     *
     * @param GetUserListQuery $getUserListQuery
     * @return JsonResponse
     */
    public function index(GetUserListQuery $getUserListQuery): JsonResponse
    {
        $this->authorize('view-any', User::class);
        $users = $getUserListQuery->handle();
        return UserResource::collection($users)
            ->response()
            ->setStatusCode(SymfonyResponse::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
