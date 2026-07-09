<?php

namespace App\Queries\User\Public;

use App\Enums\UserRole;
use App\Models\User;

class GetTeacherQuery
{
    /**
     * Retrieve detailed information about a specific active teacher.
     *
     * @param string $teacherSlug
     * @return User
     */
    public function handle(string $teacherSlug): User
    {
        return User::query()
            ->active()
            ->where('slug', $teacherSlug)
            ->whereHas('roles', fn ($query) => $query->where('name', UserRole::TEACHER->value))
            ->with(['roles'])
            ->withCount(['courses'])
            ->firstOrFail();
    }
}
