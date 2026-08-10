<?php

declare(strict_types=1);

namespace App\Actions\Lesson;

use App\Data\Lesson\Requests\CreateLessonData;
use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Throwable;

readonly class CreateLessonAction
{
    /**
     * Create a new lesson for the specified course.
     *
     * @throws InvalidArgumentException
     * @throws Throwable
     */
    public function handle(CreateLessonData $data, Course $course): Lesson
    {
        return DB::transaction(function () use ($data, $course): Lesson {
            $lessonContentClass = Relation::getMorphedModel($course->type->value);

            if (! $lessonContentClass) {
                throw new InvalidArgumentException("Unsupported lesson content type [{$course->type->value}].");
            }

            $lessonContent = $lessonContentClass::create($data->toArray());

            $lesson = $course->lessons()->make($data->toArray());
            $lesson->lessonable()->associate($lessonContent);
            $lesson->save();

            return $lesson;
        });
    }
}
