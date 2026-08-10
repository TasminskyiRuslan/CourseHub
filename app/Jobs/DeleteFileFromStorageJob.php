<?php

declare(strict_types=1);

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Throwable;

class DeleteFileFromStorageJob implements ShouldQueueAfterCommit
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
        public readonly string $disk,
        public readonly string $filePath
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Storage::disk($this->disk)->delete($this->filePath);
    }

    /**
     * Handle a job failure.
     */
    public function failed(?Throwable $exception): void
    {
        logger()->error("Failed to delete file {$this->filePath} from disk {$this->disk}", [
            'disk' => $this->disk,
            'file_path' => $this->filePath,
            'error' => $exception?->getMessage(),
        ]);
    }
}
