<?php

namespace App\Services\Lessons;

use App\Data\Lessons\CreateLessonData;
use App\Data\Lessons\UpdateLessonData;
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
                    $query->where('title', 'like', "%$value%");
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
    public function create(CreateLessonData $data, Course $course): Lesson
    {
        return DB::transaction(function () use ($data, $course) {
            $data = $data->toArray();
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
    public function update(UpdateLessonData $data, Lesson $lesson): Lesson
    {
        return DB::transaction(function () use ($data, $lesson) {
            $data = $data->toArray();
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
