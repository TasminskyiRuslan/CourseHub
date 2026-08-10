<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\UserPermission;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class LessonPolicy
{
    /**
     * Determine whether the user can create a lesson for the course.
     */
    public function create(User $user, Course $course): Response
    {
        if (! $user->can(UserPermission::LESSONS_CREATE->value)) {
            return Response::deny(__('lessons.forbidden.create'));
        }

        if (! $user->is($course->author)) {
            return Response::deny(__('lessons.forbidden.create_own'));
        }

        return Response::allow();
    }

    /**
     * Determine whether the user can update the lesson.
     */
    public function update(User $user, Lesson $lesson): Response
    {
        if (! $user->can(UserPermission::LESSONS_UPDATE_OWN->value)) {
            return Response::deny(__('lessons.forbidden.update'));
        }

        if (! $user->is($lesson->course->author)) {
            return Response::deny(__('lessons.forbidden.update_own'));
        }

        return Response::allow();
    }

    /**
     * Determine whether the user can delete the lesson.
     */
    public function delete(User $user, Lesson $lesson): Response
    {
        if ($user->can(UserPermission::LESSONS_DELETE_ALL->value)) {
            return Response::allow();
        }

        if (! $user->can(UserPermission::LESSONS_DELETE_OWN->value)) {
            return Response::deny(__('lessons.forbidden.delete'));
        }

        if (! $user->is($lesson->course->author)) {
            return Response::deny(__('lessons.forbidden.delete_own'));
        }

        return Response::allow();
    }
}
