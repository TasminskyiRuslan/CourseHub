<?php

namespace App\Policies;

use App\Enums\UserPermission;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;

class LessonPolicy
{
    public function viewAny(?User $user, Course $course): bool
    {
        return $course->is_published || $user && ($user->isAuthorOf($course) || $user->hasPermissionTo(UserPermission::COURSE_VIEW_UNPUBLISHED->value));
    }

    public function view(?User $user, Lesson $lesson): bool
    {
        return $lesson->course->is_published || $user && ($user->isAuthorOf($lesson->course) || $user->hasPermissionTo(UserPermission::COURSE_VIEW_UNPUBLISHED->value));
    }

    public function create(User $user, Course $course): bool
    {
        return $user->hasPermissionTo(UserPermission::LESSON_CREATE) && $user->isAuthorOf($course);
    }

    public function update(User $user, Lesson $lesson): bool
    {
        return $user->hasPermissionTo(UserPermission::LESSON_UPDATE_OWN->value) && $user->isAuthorOf($lesson->course);
    }

    public function delete(User $user, Lesson $lesson): bool
    {
        return $user->hasPermissionTo(UserPermission::LESSON_DELETE->value) && $user->isAuthorOf($lesson->course) || $user->hasPermissionTo(UserPermission::LESSON_DELETE_ANY->value);
    }
}
