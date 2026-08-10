<?php

declare(strict_types=1);

namespace App\Actions\Course;

use App\Models\Course;

readonly class BanCourseAction
{
    /**
     * Ban the specified course.
     */
    public function handle(Course $course): void
    {
        if ($course->isBanned()) {
            return;
        }

        $course->ban()->save();
        $course->sendBanNotification();
    }
}
