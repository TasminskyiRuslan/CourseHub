<?php

declare(strict_types=1);

namespace App\Actions\Course;

use App\Data\Course\Requests\CreateCourseData;
use App\Jobs\Course\SyncCourseWithStripeJob;
use App\Models\Course;
use App\Models\User;

readonly class CreateCourseAction
{
    /**
     * Create a new course for the specified teacher and sync with Stripe.
     */
    public function handle(CreateCourseData $data, User $teacher): Course
    {
        $course = $teacher->courses()->create($data->toArray());

        SyncCourseWithStripeJob::dispatch($course);

        return $course;
    }
}
