<?php

namespace App\Queries\User\Admin;

use App\Models\User;

class GetUserQuery
{
    /**
     * Retrieve detailed information about the specified user.
     *
     * @param string $userSlug
     * @return User
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
