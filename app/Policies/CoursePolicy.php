<?php

namespace App\Policies;

use App\Enums\UserPermission;
use App\Models\Course;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CoursePolicy
{
    /**
     * Determine whether the user can create a course.
     *
     * @param User $user
     * @return Response
     */
    public function create(User $user): Response
    {
        if (!$user->can(UserPermission::COURSES_CREATE->value)) {
            return Response::deny(__('You do not have permission to create courses.'));
        }

        return Response::allow();
    }

    /**
     * Determine whether the user can update the course.
     *
     * @param User $user
     * @param Course $course
     * @return Response
     */
    public function update(User $user, Course $course): Response
    {
        if (!$user->can(UserPermission::COURSES_UPDATE_OWN->value)) {
            return Response::deny(__('You do not have permission to update courses.'));
        }

        if (!$user->is($course->author)) {
            return Response::deny(__('You can only update your own courses.'));
        }

        return Response::allow();
    }

    /**
     * Determine whether the user can publish the course.
     *
     * @param User $user
     * @param Course $course
     * @return Response
     */
    public function publish(User $user, Course $course): Response
    {
        if (!$user->can(UserPermission::COURSES_PUBLISH_OWN->value)) {
            return Response::deny(__('You do not have permission to publish courses.'));
        }

        if (!$user->is($course->author)) {
            return Response::deny(__('You can only publish your own courses.'));
        }

        return Response::allow();
    }

    /**
     * Determine whether the user can delete the course.
     *
     * @param User $user
     * @param Course $course
     * @return Response
     */
    public function delete(User $user, Course $course): Response
    {
        if ($user->can(UserPermission::COURSES_DELETE_ALL->value)) {
            return Response::allow();
        }

        if (!$user->can(UserPermission::COURSES_DELETE_OWN->value)) {
            return Response::deny(__('You do not have permission to delete courses.'));
        }

        if (!$user->is($course->author)) {
            return Response::deny(__('You can only delete your own courses.'));
        }

        return Response::allow();
    }

    /**
     * Determine whether the user can ban the course.
     *
     * @param User $user
     * @return Response
     */
    public function ban(User $user): Response
    {
        if (!$user->can(UserPermission::COURSES_BAN_ALL->value)) {
            return Response::deny(__('You do not have permission to ban courses.'));
        }

        return Response::allow();
    }

    /**
     * Determine whether the user can check out or enroll in the course.
     *
     * @param User $user
     * @param Course $course
     * @return Response
     */
    public function checkout(User $user, Course $course): Response
    {
        if ($user->is($course->author)) {
            return Response::deny(__('You cannot enroll in or purchase your own course.'));
        }

        return Response::allow();
    }
}
