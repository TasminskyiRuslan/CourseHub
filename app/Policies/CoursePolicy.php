<?php

namespace App\Policies;

use App\Enums\UserPermission;
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
        if ($course->is_published) {
            return true;
        }

        if (! $user) {
            return false;
        }

        if ($user->isAuthorOf($course)) {
            return true;
        }

        return $user->can(UserPermission::COURSE_VIEW_UNPUBLISHED->value);
    }

    public function create(User $user): bool
    {
        return $user->can(UserPermission::COURSE_CREATE->value);
    }

    public function update(User $user, Course $course): bool
    {
        return $user->hasPermissionTo(UserPermission::COURSE_UPDATE->value) && $user->isAuthorOf($course);
    }

    public function delete(User $user, Course $course): bool
    {
        return $user->hasPermissionTo(UserPermission::COURSE_DELETE->value) && $user->isAuthorOf($course) || $user->hasPermissionTo(UserPermission::COURSE_DELETE_ANY->value);
    }

    public function publish(User $user, Course $course): bool
    {
        return $user->hasPermissionTo(UserPermission::COURSE_PUBLISH->value) && $user->isAuthorOf($course);
    }

    public function unpublish(User $user, Course $course): bool
    {
        return $user->hasPermissionTo(UserPermission::COURSE_UNPUBLISH->value) && $user->isAuthorOf($course) || $user->hasPermissionTo(UserPermission::COURSE_UNPUBLISH_ANY->value);
    }
}
