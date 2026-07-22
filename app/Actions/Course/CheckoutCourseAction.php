<?php

namespace App\Actions\Course;

use App\Data\Course\Results\CheckoutResultData;
use App\Enums\CheckoutStatus;
use App\Models\Course;
use App\Models\User;
use Illuminate\Validation\ValidationException;

readonly class CheckoutCourseAction
{
    /**
     * @param EnrollUserAction $enrollUserAction
     */
    public function __construct(
        private EnrollUserAction $enrollUserAction
    ) {}

    /**
     * Check out the specified course for the specified user.
     *
     * @param User $user
     * @param string $courseSlug
     * @return CheckoutResultData
     */
    public function handle(User $user, string $courseSlug): CheckoutResultData
    {
        $course = Course::query()
            ->active()
            ->where('slug', $courseSlug)
            ->firstOrFail();

        if ($user->is($course->author)) {
            throw ValidationException::withMessages([
                'course' => __('You cannot enroll in or purchase your own course.'),
            ]);
        }

        if ($user->isEnrolledIn($course)) {
            return new CheckoutResultData(
                status: CheckoutStatus::ENROLLED,
                courseId: $course->id,
                checkoutUrl: null
            );
        }

        if ($course->isFree()) {
            $this->enrollUserAction->handle($user, $course);

            return new CheckoutResultData(
                status: CheckoutStatus::ENROLLED,
                courseId: $course->id,
                checkoutUrl: null
            );
        }

        $checkout = $user->checkout([$course->stripe_price_id => 1], [
            'success_url' => config('app.frontend_url') . "/courses/{$course->slug}?status=success",
            'cancel_url' => config('app.frontend_url') . "/courses/{$course->slug}?status=cancelled",
            'metadata' => [
                'user_id' => $user->id,
                'course_id' => $course->id,
            ],
        ]);

        return new CheckoutResultData(
            status: CheckoutStatus::PAYMENT_REQUIRED,
            courseId: $course->id,
            checkoutUrl: $checkout->url
        );
    }
}
