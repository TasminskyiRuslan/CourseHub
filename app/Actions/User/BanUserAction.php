<?php

namespace App\Actions\User;

use App\Actions\Auth\RevokeAllTokensAction;
use App\Models\User;

class BanUserAction
{
    /**
     * @param RevokeAllTokensAction $revokeAllTokensAction
     */
    public function __construct(
        protected RevokeAllTokensAction $revokeAllTokensAction
    ) {}

    /**
     * Ban the specified user.
     *
     * @param User $user
     * @return void
     */
    public function handle(User $user): void
    {
        if ($user->isBanned()) {
            return;
        }

        $user->ban()->save();
        $this->revokeAllTokensAction->handle($user);
        $user->sendBanNotification();
    }
}
