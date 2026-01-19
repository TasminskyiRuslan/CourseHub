<?php

namespace App\Services;

use App\DTO\CreateCourseDTO;
use App\DTO\CourseFilterDTO;
use App\DTO\UpdateCourseDTO;
use App\Models\Course;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Throwable;

class CourseService
{
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
            ->with(['author', 'lessons.lessonable'])
            ->paginate(config('pagination.courses_per_page'));
    }


    /**
     * @throws Throwable
     */
    public function create(CreateCourseDTO $dto, User $author): Course
    {
        return DB::transaction(function () use ($dto, $author) {
            $data = $dto->toArray();
            if ($dto->image) {
                $data['image_url'] = $dto->image->store('courses', 'public');
            }
            return $author->courses()->create($data);
        });
    }

    /**
     * @throws Throwable
     */
    public function update(UpdateCourseDTO $dto, Course $course): Course
    {
        return DB::transaction(function () use ($dto, $course) {
            $data = $dto->toArray();
            if ($dto->image) {
                if ($course->image_url) {
                    Storage::disk('public')->delete($course->image_url);
                }
                $data['image_url'] = $dto->image->store('courses', 'public');
            }
            $course->update($data);
            return $course;
        });
    }

    /**
     * @throws Throwable
     */
    public function delete(Course $course): void
    {
        DB::transaction(function () use ($course) {
            if ($course->image_url) {
                Storage::disk('public')->delete($course->image_url);
            }
            $course->delete();
        });
    }
}
