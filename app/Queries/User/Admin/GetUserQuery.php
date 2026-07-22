<?php

namespace App\Queries\User\Admin;

use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

readonly class GetUserQuery
{
    /**
     * Retrieve detailed information about the specified user.
     *
     * @param string $userSlug
     * @return User
     * @throws ModelNotFoundException
     */
    public function handle(string $userSlug): User
    {
        return User::query()
            ->where('slug', $userSlug)
            ->with(['roles'])
            ->withCount(['courses' => fn($q) => $q->withTrashed()])
            ->withTrashed()
            ->firstOrFail();
    }
}
