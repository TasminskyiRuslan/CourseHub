<?php

declare(strict_types=1);

namespace App\Loaders\User\Admin;

use App\Models\User;
use Illuminate\Contracts\Database\Eloquent\Builder;

readonly class UserLoader
{
    /**
     * Eager load relations for the specified user for administrator.
     */
    public function handle(User $user): User
    {
        return $user->load(['roles'])
            ->loadCount(['courses' => fn (Builder $query): Builder => $query->withTrashed()]);
    }
}
