<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\User;

class CoursePolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Course $course): bool
    {
        return $course->isVisibleFor($user);
    }

    public function create(User $user): bool
    {
        return $user->canPublishContent();
    }

    public function update(User $user, Course $course): bool
    {
        return $user->isAuthorOf($course);
    }

    public function delete(User $user, Course $course): bool
    {
        return $user->isAdmin() || $user->isAuthorOf($course);
    }

    public function restore(User $user, Course $course): bool
    {
        return false;
    }

    public function forceDelete(User $user, Course $course): bool
    {
        return false;
    }

    public function publish(User $user, Course $course): bool
    {
        return $user->isAuthorOf($course);
    }

    public function unpublish(User $user, Course $course): bool
    {
        return $user->isAuthorOf($course) || $user->isAdmin();
    }
}
