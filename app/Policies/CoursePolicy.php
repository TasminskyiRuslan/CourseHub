<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\UserPermission;
use App\Models\Course;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CoursePolicy
{
    /**
     * Determine whether the user can create a course.
     */
    public function create(User $user): Response
    {
        if (! $user->can(UserPermission::COURSES_CREATE->value)) {
            return Response::deny(__('courses.forbidden.create'));
        }

        return Response::allow();
    }

    /**
     * Determine whether the user can update the course.
     */
    public function update(User $user, Course $course): Response
    {
        if (! $user->can(UserPermission::COURSES_UPDATE_OWN->value)) {
            return Response::deny(__('courses.forbidden.update'));
        }

        if (! $user->is($course->author)) {
            return Response::deny(__('courses.forbidden.update_own'));
        }

        return Response::allow();
    }

    /**
     * Determine whether the user can publish the course.
     */
    public function publish(User $user, Course $course): Response
    {
        if (! $user->can(UserPermission::COURSES_PUBLISH_OWN->value)) {
            return Response::deny(__('courses.forbidden.publish'));
        }

        if (! $user->is($course->author)) {
            return Response::deny(__('courses.forbidden.publish_own'));
        }

        return Response::allow();
    }

    /**
     * Determine whether the user can delete the course.
     */
    public function delete(User $user, Course $course): Response
    {
        if ($user->can(UserPermission::COURSES_DELETE_ALL->value)) {
            return Response::allow();
        }

        if (! $user->can(UserPermission::COURSES_DELETE_OWN->value)) {
            return Response::deny(__('courses.forbidden.delete'));
        }

        if (! $user->is($course->author)) {
            return Response::deny(__('courses.forbidden.delete_own'));
        }

        return Response::allow();
    }

    /**
     * Determine whether the user can ban the course.
     */
    public function ban(User $user): Response
    {
        if (! $user->can(UserPermission::COURSES_BAN_ALL->value)) {
            return Response::deny(__('courses.forbidden.ban'));
        }

        return Response::allow();
    }

    /**
     * Determine whether the user can check out or enroll in the course.
     */
    public function checkout(User $user, Course $course): Response
    {
        if ($user->is($course->author)) {
            return Response::deny(__('courses.forbidden.checkout_own'));
        }

        return Response::allow();
    }
}
