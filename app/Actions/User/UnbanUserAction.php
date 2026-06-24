<?php

namespace App\Actions\User;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Throwable;

class UnbanUserAction
{
    /**
     * Unban the specified user.
     *
     * @param User $user
     * @return void
     * @throws Throwable
     */
    public function handle(User $user): void
    {
        if (!$user->isBanned()) {
            return;
        }

        DB::transaction(function () use ($user) {
            $user->unban()->save();

            DB::afterCommit(function () use ($user) {
                $user->sendUnbanNotification();
            });
        });
    }
}
