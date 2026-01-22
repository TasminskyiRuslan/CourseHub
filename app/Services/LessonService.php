<?php

namespace App\Services;

use App\DTO\LessonDTO;
use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Throwable;

class LessonService
{
    public function search(Course $course): LengthAwarePaginator
    {
        return QueryBuilder::for(Lesson::class)
            ->where('course_id', $course->id)
            ->allowedFilters([
                AllowedFilter::callback('search', function ($query, $value) {
                    $query->where('title', 'like', "%{$value}%");
                }),
            ])
            ->allowedSorts(['title', 'position', 'created_at'])
            ->defaultSort('position')
            ->with('lessonable')
            ->paginate(config('pagination.lessons_per_page'));
    }

    /**
     * @throws Throwable
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
            return $lesson->load('lessonable');
        });
    }

    /**
     * @throws Throwable
     */
    public function update(Lesson $lesson, LessonDTO $dto): Lesson
    {
        return DB::transaction(function () use ($lesson, $dto) {
            $data = $dto->toArray();
            $lesson->update($data);
            $lesson->lessonable->update($data);
            return $lesson->fresh('lessonable');
        });
    }

    /**
     * @throws Throwable
     */
    public function delete(Lesson $lesson): void
    {
        DB::transaction(function () use ($lesson) {
            $lesson->lessonable()->delete();
            $lesson->delete();
        });
    }
}
