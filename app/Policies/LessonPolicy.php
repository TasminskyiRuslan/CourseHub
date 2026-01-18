<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;

class LessonPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

//    public function view(?User $user, Lesson $lesson): bool
//    {
//
//        return $user->id === $lesson->course->user_id;
//    }

    public function create(User $user, Course $course): bool
    {
        return $user->isAuthorOf($course);
    }

}
