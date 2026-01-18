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
use Throwable;

class CourseService
{
    public function search(CourseFilterDTO $filters): LengthAwarePaginator
    {
        return Course::query()
            ->where('courses.is_published', true)
            ->filter($filters)
            ->sort($filters)
            ->with(['author', 'lessons.lessonable'])
            ->paginate(config('courses.per_page'));
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
