<?php

namespace App\Policies;

use App\Enums\UserPermission;
use App\Models\Course;
use App\Models\User;

class CoursePolicy
{
    /**
     * Determine whether the user can create the course.
     *
     * @param User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return $user->can(UserPermission::COURSES_CREATE->value);
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
        return $user->can(UserPermission::COURSES_UPDATE_OWN->value)
            && $user->is($course->author);
    }

    /**
     * Determine whether the user can publish the course.
     *
     * @param User $user
     * @param Course $course
     * @return bool
     */
    public function publish(User $user, Course $course): bool
    {
        return $user->can(UserPermission::COURSES_PUBLISH_OWN->value)
            && $user->is($course->author);
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
        if ($user->can(UserPermission::COURSES_DELETE_ALL->value)) {
            return true;
        }

        return $user->can(UserPermission::COURSES_DELETE_OWN->value)
            && $user->is($course->author);
    }

    /**
     * Determine whether the user can ban the course.
     *
     * @param User $user
     * @return bool
     */
    public function ban(User $user): bool
    {
        return $user->can(UserPermission::COURSES_BAN_ALL->value);
    }
}
