<?php

declare(strict_types=1);

namespace App\Loaders\User\Public;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

readonly class TeacherLoader
{
    /**
     * Eager load relations for the specified teacher for public context.
     */
    public function handle(User $user): User
    {
        return $user->loadCount([
            'courses' => fn (Builder $query): Builder => $query->active(),
        ]);
    }
}
