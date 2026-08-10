<?php

declare(strict_types=1);

namespace App\Actions\Lesson;

use App\Data\Lesson\Requests\UpdateLessonData;
use App\Models\Lesson;
use Illuminate\Support\Facades\DB;
use Throwable;

readonly class UpdateLessonAction
{
    /**
     * Update the specified lesson and its content.
     *
     * @throws Throwable
     */
    public function handle(UpdateLessonData $data, Lesson $lesson): Lesson
    {
        return DB::transaction(function () use ($data, $lesson): Lesson {
            $lesson->update($data->toArray());
            $lesson->lessonable?->update($data->toArray());

            return $lesson;
        });
    }
}
