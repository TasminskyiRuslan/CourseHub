<?php

namespace App\Policies;

use App\Enums\UserPermission;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class LessonPolicy
{
    /**
     * Determine whether the user can create a lesson.
     *
     * @param User $user
     * @param Course $course
     * @return bool
     */
    public function create(User $user, Course $course): bool
    {
        return $user->can(UserPermission::LESSONS_CREATE->value)
            && $user->is($course->author);
    }

    /**
     * Determine whether the user can update the lesson.
     *
     * @param User $user
     * @param Lesson $lesson
     * @return bool
     */
    public function update(User $user, Lesson $lesson): bool
    {
        return $user->can(UserPermission::LESSONS_UPDATE_OWN->value)
            && $user->is($lesson->course->author);
    }

    /**
     * Determine whether the user can delete the lesson.
     *
     * @param User $user
     * @param Lesson $lesson
     * @return bool
     */
    public function delete(User $user, Lesson $lesson): bool
    {
        if ($user->can(UserPermission::LESSONS_DELETE_ALL->value)) {
            return true;
        }

        return $user->can(UserPermission::LESSONS_DELETE_OWN->value)
            && $user->is($lesson->course->author);
    }
}
