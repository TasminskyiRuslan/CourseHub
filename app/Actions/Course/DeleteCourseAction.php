<?php

declare(strict_types=1);

namespace App\Actions\Course;

use App\Jobs\Course\ArchiveCourseInStripeJob;
use App\Models\Course;

readonly class DeleteCourseAction
{
    /**
     * Delete the specified course and archive Stripe product.
     */
    public function handle(Course $course): void
    {
        if ($course->stripe_product_id) {
            ArchiveCourseInStripeJob::dispatch($course->stripe_product_id);
        }

        $course->delete();
    }
}
