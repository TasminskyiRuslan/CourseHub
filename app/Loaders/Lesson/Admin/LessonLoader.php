<?php

declare(strict_types=1);

namespace App\Loaders\Lesson\Admin;

use App\Models\Lesson;
use Illuminate\Database\Eloquent\Relations\MorphTo;

readonly class LessonLoader
{
    /**
     * Eager load relations for the specified lesson for administrator.
     */
    public function handle(Lesson $lesson): Lesson
    {
        return $lesson->load([
            'lessonable' => fn (MorphTo $morphTo): MorphTo => $morphTo->withTrashed(),
        ]);
    }
}
