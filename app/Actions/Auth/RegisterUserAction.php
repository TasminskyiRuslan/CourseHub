<?php

namespace App\Actions\Auth;

use App\Data\Auth\AuthData;
use App\Data\Auth\RegisterData;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\DB;
use Throwable;

class RegisterUserAction
{
    public function __construct(
        protected IssueTokenAction $issueTokenAction,
    )
    {
    }

    /**
     * @throws Throwable
     */
    public function handle(RegisterData $data): AuthData
    {
        return DB::transaction(function () use ($data) {
            $user = User::create($data->toArray());

            event(new Registered($user));

            return $this->issueTokenAction->handle($user, $data->remember);
        });
    }
}
