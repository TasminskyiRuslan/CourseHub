<?php

namespace App\Actions\Lesson;

use App\Data\Lesson\CreateLessonData;
use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\DB;
use Throwable;

class CreateLessonAction
{
    /**
     * Create a new lesson.
     *
     * @param CreateLessonData $lessonData
     * @param Course $course
     * @return Lesson
     * @throws Throwable
     */
    public function handle(CreateLessonData $lessonData, Course $course): Lesson
    {
        return DB::transaction(function () use ($lessonData, $course) {
            $lessonContentClass = Relation::getMorphedModel($course->type->value);
            $lessonContent = $lessonContentClass::create($lessonData->all());
            $lesson = $course->lessons()->make($lessonData->all());
            $lesson->lessonable()->associate($lessonContent);
            $lesson->save();
            return $lesson->load('lessonable');
        });
    }
}
