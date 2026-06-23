<?php

namespace App\Actions\User;

use App\Actions\Auth\RevokeAllTokensAction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Throwable;

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
     * @throws Throwable
     */
    public function handle(User $user): void
    {
        if ($user->isBanned()) {
            return;
        }

        DB::transaction(function () use ($user) {
            $user->ban()->save();
            $this->revokeAllTokensAction->handle($user);

            DB::afterCommit(function () use ($user) {
                $user->sendBanNotification();
            });
        });
    }
}
