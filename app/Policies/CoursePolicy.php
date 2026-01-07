<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\User;

class CoursePolicy
{
    private function canUserPublish(User $user): bool
    {
        return $user->isTeacher() && $user->hasVerifiedEmail();
    }

    public function create(User $user): bool {
        return $this->canUserPublish($user);
    }

    public function update(User $user, Course $course): bool
    {
        return $this->canUserPublish($user) && $user->isOwnerOf($course);
    }

    public function delete(User $user, Course $course): bool
    {
        return $user->isAdmin() || ($this->canUserPublish($user) && $user->isOwnerOf($course));
    }
}
