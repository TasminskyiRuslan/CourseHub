<?php

declare(strict_types=1);

namespace App\Jobs\Course;

use App\Actions\Course\ArchiveStripeCourseAction;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Stripe\Exception\ApiErrorException;
use Throwable;

class ArchiveCourseInStripeJob implements ShouldQueueAfterCommit
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
        public readonly string $stripeProductId
    ) {}

    /**
     * Execute the job.
     *
     * @throws ApiErrorException
     */
    public function handle(ArchiveStripeCourseAction $action): void
    {
        $action->handle($this->stripeProductId);
    }

    /**
     * Handle a job failure.
     */
    public function failed(?Throwable $exception): void
    {
        logger()->error("Failed to archive course {$this->stripeProductId} in Stripe", [
            'course_id' => $this->stripeProductId,
            'error' => $exception?->getMessage(),
        ]);
    }
}
