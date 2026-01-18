<?php

namespace App\Services;

use App\DTO\LessonDTO;
use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\DB;

class LessonService
{

    /**
     * @throws \Throwable
     */
    public function create(Course $course, LessonDTO $dto): Lesson
    {
        return DB::transaction(function () use ($course, $dto) {
            $data = $dto->toArray();
            $lessonContentClass = Relation::getMorphedModel($course->type->value);
            $lessonContent = $lessonContentClass::create($data);
            $lesson = $course->lessons()->make($data);
            $lesson->lessonable()->associate($lessonContent);
            $lesson->save();
            return $lesson;
        });
    }
}
