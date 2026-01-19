<?php

namespace App\Services;

use App\DTO\CourseFilterDTO;
use App\DTO\LessonDTO;
use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class LessonService
{
    public function search(Course $course): LengthAwarePaginator
    {
        return QueryBuilder::for($course->lessons())
            ->allowedFilters([
                'type',
                AllowedFilter::callback('search', function ($query, $value) {
                    $query->orWhere('description', 'like', "%{$value}%");
                }),
            ])
            ->allowedSorts(['title', 'position', 'created_at'])
            ->defaultSort('position')
            ->with('lessonable')
            ->paginate(config('pagination.lessons_per_page'));
    }

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
