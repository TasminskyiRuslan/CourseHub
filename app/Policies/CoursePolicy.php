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
        return $course->is_published || $user && ($user->isAuthorOf($course) || $user->hasPermissionTo(UserPermission::COURSE_VIEW_UNPUBLISHED->value));
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(UserPermission::COURSE_CREATE->value) && $user->hasVerifiedEmail();
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
