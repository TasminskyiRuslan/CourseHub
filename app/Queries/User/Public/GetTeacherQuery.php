<?php

namespace App\Queries\User\Public;

use App\Enums\UserRole;
use App\Models\User;

class GetTeacherQuery
{
    /**
     * Retrieve detailed information about a specific teacher.
     *
     * @param string $teacherSlug
     * @return User
     */
    public function handle(string $teacherSlug): User
    {
        return User::query()
            ->active()
            ->where('slug', $teacherSlug)
            ->whereHas('roles', function ($query) {
                $query->where('name', UserRole::TEACHER->value);
            })
            ->withCount(['courses' => fn($query) => $query->active()])
            ->firstOrFail();
    }
}
