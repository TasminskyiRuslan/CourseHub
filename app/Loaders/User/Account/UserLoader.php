<?php

declare(strict_types=1);

namespace App\Loaders\User\Account;

use App\Models\User;

readonly class UserLoader
{
    /**
     * Eager load relations for the specified user for account.
     */
    public function handle(User $user): User
    {
        return $user->load(['roles']);
    }
}
