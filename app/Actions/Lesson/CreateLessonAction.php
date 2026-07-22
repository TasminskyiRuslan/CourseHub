<?php

namespace App\Actions\Lesson;

use App\Data\Lesson\Requests\CreateLessonData;
use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\DB;
use Throwable;

readonly class CreateLessonAction
{
    /**
     * Create a new lesson for the specified course.
     *
     * @param CreateLessonData $data
     * @param Course $course
     * @return Lesson
     * @throws Throwable
     */
    public function handle(CreateLessonData $data, Course $course): Lesson
    {
        return DB::transaction(function () use ($data, $course) {
            $lessonContentClass = Relation::getMorphedModel($course->type->value);
            $lessonContent = $lessonContentClass::create($data->all());

            $lesson = $course->lessons()->make($data->all());
            $lesson->lessonable()->associate($lessonContent);
            $lesson->save();

            return $lesson;
        });
    }
}
