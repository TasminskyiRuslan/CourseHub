<?php

namespace App\Actions\Course;

use App\Models\Course;
use Illuminate\Support\Facades\DB;
use Throwable;

readonly class UnbanCourseAction
{
    /**
     * Unban the specified course.
     *
     * @param Course $course
     * @return void
     * @throws Throwable
     */
    public function handle(Course $course): void
    {
        if (!$course->isBanned()) {
            return;
        }

        DB::transaction(function () use ($course) {
            $course->unban()->save();

            DB::afterCommit(function () use ($course) {
                $course->sendUnbanNotification();
            });
        });
    }
}
