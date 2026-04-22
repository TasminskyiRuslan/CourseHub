<?php

namespace App\Policies;

use App\Enums\UserPermission;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;

class LessonPolicy
{
    /**
     * Determine whether the user can view the list of course lessons.
     *
     * @param User|null $user
     * @param Course $course
     * @return bool
     */
    public function viewAny(?User $user, Course $course): bool
    {
        if ($course->is_published) {
            return true;
        }

        if (!$user) {
            return false;
        }

        if ($user->is($course->author)) {
            return true;
        }

        return $user->can(UserPermission::COURSE_VIEW_UNPUBLISHED->value);
    }

    /**
     * Determine whether the user can view the specific lesson's details.
     *
     * @param User|null $user
     * @param Lesson $lesson
     * @return bool
     */
    public function view(?User $user, Lesson $lesson): bool
    {
        if ($lesson->course->is_published) {
            return true;
        }

        if (!$user) {
            return false;
        }

        if ($user->is($lesson->course->author)) {
            return true;
        }

        return $user->can(UserPermission::COURSE_VIEW_UNPUBLISHED->value);
    }

    /**
     * Determine whether the user can create a lesson.
     *
     * @param User $user
     * @param Course $course
     * @return bool
     */
    public function create(User $user, Course $course): bool
    {
        return $user->can(UserPermission::LESSON_CREATE->value) && $user->is($course->author);
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
        return $user->can(UserPermission::LESSON_EDIT_OWN->value) && $user->is($lesson->course->author);
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
        if ($user->can(UserPermission::LESSON_DELETE_ANY->value)) {
            return true;
        }

        return $user->can(UserPermission::LESSON_DELETE_OWN->value) && $user->is($lesson->course->author);
    }
}
