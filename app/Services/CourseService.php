<?php

namespace App\Services;

use App\DTO\CreateCourseDTO;
use App\DTO\UpdateCourseDTO;
use App\Models\Course;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Throwable;

class CourseService
{
    public function __construct(
        protected CourseImageService $imageService,
    )
    {
    }

    public function search(): LengthAwarePaginator
    {
        return QueryBuilder::for(Course::class)
            ->allowedFilters([
                'type',
                AllowedFilter::callback('search', function ($query, $value) {
                    $query->where(function ($q) use ($value) {
                        $q->where('title', 'like', "%{$value}%")
                            ->orWhere('description', 'like', "%{$value}%");
                    });
                }),
                AllowedFilter::callback('author', function ($query, $value) {
                    $query->whereHas('author', function ($q) use ($value) {
                        $q->where('slug', $value);
                    });
                }),
            ])
            ->allowedSorts(['title', 'price', 'created_at'])
            ->defaultSort('-created_at')
            ->where('is_published', true)
            ->with('author')
            ->paginate(config('pagination.courses_per_page'));
    }


    /**
     * @throws Throwable
     */
    public function create(CreateCourseDTO $dto, User $author): Course
    {
        $course = $author->courses()->create($dto->toArray());
        return $course->load('author');
    }

    /**
     * @throws Throwable
     */
    public function update(UpdateCourseDTO $dto, Course $course): Course
    {
        $course->update($dto->toArray());
        return $course->fresh('author');
    }

    /**
     * @throws Throwable
     */
    public function delete(Course $course): void
    {
        DB::transaction(function () use ($course) {
            $this->imageService->delete($course);
            $course->delete();
        });
    }
}
