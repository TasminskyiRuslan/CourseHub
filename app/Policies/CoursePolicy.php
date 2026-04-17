<?php

namespace App\Policies;

use App\Enums\UserPermission;
use App\Models\Course;
use App\Models\User;

class CoursePolicy
{
    /**
     * Determine whether the user can view the list of courses.
     *
     * @param User|null $user
     * @return bool
     */
    public function viewAny(?User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the specific course's details.
     *
     * @param User|null $user
     * @param Course $course
     * @return bool
     */
    public function view(?User $user, Course $course): bool
    {
        if ($course->is_published) {
            return true;
        }

        if (!$user) {
            return false;
        }

        if ($user->isAuthorOf($course)) {
            return true;
        }

        return $user->can(UserPermission::COURSE_VIEW_UNPUBLISHED->value);
    }

    /**
     * Determine whether the user can create courses.
     *
     * @param User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return $user->can(UserPermission::COURSE_CREATE->value);
    }

    /**
     * Determine whether the user can update the course.
     *
     * @param User $user
     * @param Course $course
     * @return bool
     */
    public function update(User $user, Course $course): bool
    {
        return $user->can(UserPermission::COURSE_EDIT_OWN->value) && $user->isAuthorOf($course);
    }

    /**
     * Determine whether the user can delete the course.
     *
     * @param User $user
     * @param Course $course
     * @return bool
     */
    public function delete(User $user, Course $course): bool
    {
        if ($user->can(UserPermission::COURSE_DELETE_ANY->value)) {
            return true;
        }

        return $user->can(UserPermission::COURSE_DELETE_OWN->value) && $user->isAuthorOf($course);
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
