<?php

declare(strict_types=1);

namespace App\Actions\Lesson;

use App\Models\Lesson;
use Illuminate\Support\Facades\DB;
use Throwable;

readonly class DeleteLessonAction
{
    /**
     * Delete the specified lesson and its content.
     *
     * @throws Throwable
     */
    public function handle(Lesson $lesson): void
    {
        DB::transaction(function () use ($lesson): void {
            $lesson->lessonable?->delete();
            $lesson->delete();
        });
    }
}
