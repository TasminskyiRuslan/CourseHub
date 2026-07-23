<?php

namespace App\Actions\Course;

use App\Data\Course\Requests\UpdateCourseData;
use App\Models\Course;
use Illuminate\Support\Facades\DB;
use Stripe\StripeClient;
use Throwable;

readonly class UpdateCourseAction
{
    /**
     * @param SyncCourseWithStripeAction $syncCourseWithStripeAction
     */
    public function __construct(
        private SyncCourseWithStripeAction $syncCourseWithStripeAction
    ) {}

    /**
     * Update the specified course.
     *
     * @param UpdateCourseData $data
     * @param Course $course
     * @return Course
     * @throws Throwable
     */
    public function handle(UpdateCourseData $data, Course $course): Course
    {
        DB::transaction(function () use ($course, $data) {
            $course->update($data->all());
        });

        $this->syncCourseWithStripeAction->handle($course);

        return $course;
    }
}
