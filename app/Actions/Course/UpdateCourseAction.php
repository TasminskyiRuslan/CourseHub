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
     * @param StripeClient $stripe
     */
    public function __construct(
        private StripeClient $stripe
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
        return DB::transaction(function () use ($course, $data) {
            $course->update($data->all());
            $course->syncWithStripe($this->stripe);

            return $course;
        });
    }
}
