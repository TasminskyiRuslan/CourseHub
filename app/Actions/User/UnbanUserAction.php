<?php

declare(strict_types=1);

namespace App\Actions\User;

use App\Models\User;

readonly class UnbanUserAction
{
    /**
     * Unban the specified user.
     */
    public function handle(User $user): void
    {
        if (! $user->isBanned()) {
            return;
        }

        $user->unban()->save();
        $user->sendUnbanNotification();
    }
}
