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
     * Determine whether the user can view the list of course lessons.
     *
     * @param User|null $user
     * @param Course $course
     * @return bool
     */
    public function viewAny(?User $user, Course $course): bool
    {
        return Gate::allows('view', $course);
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
        return Gate::allows('view', $lesson->course);
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
        return $user->is($lesson->course->author);
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

        return $user->is($lesson->course->author);
    }
}
