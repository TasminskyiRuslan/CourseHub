<?php

namespace App\Actions\User;

use App\Models\User;

class UnbanUserAction
{
    /**
     * Unban the specified user.
     *
     * @param User $user
     * @return void
     */
    public function handle(User $user): void
    {
        $user->unban()->save();
        $user->sendUnbanNotification();
    }
}
