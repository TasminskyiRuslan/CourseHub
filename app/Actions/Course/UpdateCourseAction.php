<?php

declare(strict_types=1);

namespace App\Actions\Course;

use App\Data\Course\Requests\UpdateCourseData;
use App\Jobs\Course\SyncCourseWithStripeJob;
use App\Models\Course;

readonly class UpdateCourseAction
{
    /**
     * Update the specified course and sync with Stripe.
     */
    public function handle(UpdateCourseData $data, Course $course): Course
    {
        $course->update($data->toArray());

        SyncCourseWithStripeJob::dispatch($course);

        return $course;
    }
}
