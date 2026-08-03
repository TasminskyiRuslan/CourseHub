<?php

namespace App\Actions\Course;

use App\Data\Course\Requests\CreateCourseData;
use App\Models\Course;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Stripe\StripeClient;
use Throwable;

readonly class CreateCourseAction
{
    /**
     * @param SyncCourseWithStripeAction $syncCourseWithStripeAction
     */
    public function __construct(
        private SyncCourseWithStripeAction $syncCourseWithStripeAction
    ) {}

    /**
     * Create a new course for the specified teacher and sync with Stripe.
     *
     * @param CreateCourseData $data
     * @param User $teacher
     * @return Course
     * @throws Throwable
     */
    public function handle(CreateCourseData $data, User $teacher): Course
    {
        return DB::transaction(function () use ($data, $teacher) {
            $course = $teacher->courses()->create($data->toArray());

            $this->syncCourseWithStripeAction->handle($course);

            return $course;
        });
    }
}
