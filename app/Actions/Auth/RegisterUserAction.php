<?php

namespace App\Actions\Auth;

use App\DTO\Auth\AuthDTO;
use App\DTO\Auth\RegisterDTO;
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
    public function handle(RegisterDTO $dto): AuthDTO
    {
        return DB::transaction(function () use ($dto) {
            $user = User::create($dto->toArray());

            event(new Registered($user));

            $remember = $dto->remember ?? false;

            return $this->issueTokenAction->handle($user, $remember);
        });
    }
}
