<?php

declare(strict_types=1);

namespace App\Jobs\Course;

use App\Actions\Course\SyncCourseWithStripeAction;
use App\Models\Course;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Stripe\Exception\ApiErrorException;
use Throwable;

class SyncCourseWithStripeJob implements ShouldQueueAfterCommit
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public array $backoff = [10, 30, 60];

    /**
     * Create a new job instance.
     */
    public function __construct(
        public readonly Course $course
    ) {}

    /**
     * Execute the job.
     *
     * @throws ApiErrorException
     */
    public function handle(SyncCourseWithStripeAction $action): void
    {
        $action->handle($this->course);
    }

    /**
     * Handle a job failure.
     */
    public function failed(?Throwable $exception): void
    {
        logger()->error("Failed to sync course {$this->course->id} with Stripe", [
            'course_id' => $this->course->id,
            'error' => $exception?->getMessage(),
        ]);
    }
}
