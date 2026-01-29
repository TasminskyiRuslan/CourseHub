<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;

class LessonPolicy
{
    public function viewAny(?User $user, Course $course): bool
    {
        return $course->isVisibleFor($user);
    }

    public function view(?User $user, Lesson $lesson): bool
    {
        return $lesson->course->isVisibleFor($user);
    }

    public function create(User $user, Course $course): bool
    {
        return $user->canPublishContent() && $user->isAuthorOf($course);
    }

    public function update(User $user, Lesson $lesson): bool
    {
        return $user->isAuthorOf($lesson->course);
    }

    public function delete(User $user, Lesson $lesson): bool
    {
        return $user->isAdmin() || $user->isAuthorOf($lesson->course);
    }

    public function restore(User $user, Lesson $lesson): bool
    {
        return false;
    }

    public function forceDelete(User $user, Lesson $lesson): bool
    {
        return false;
    }
}
