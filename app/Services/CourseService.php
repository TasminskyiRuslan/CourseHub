<?php

namespace App\Services;

use App\DTO\CourseDTO;
use App\DTO\CourseFilterDTO;
use App\Models\Course;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CourseService
{
    public function getList(CourseFilterDTO $filters): LengthAwarePaginator
    {
        return Course::query()
            ->where('courses.is_published', true)
            ->filter($filters)
            ->sort($filters)
            ->with(['author'])
            ->paginate(config('courses.per_page'));
    }


    public function store(CourseDTO $dto, User $author): Course
    {
        $data = $dto->toArray();
        if ($dto->image) {
            $data['image_url'] = $dto->image->store('courses', 'public');
        }
        return $author->courses()->create($data);
    }

    public function show(Course $course): Course
    {
        $user = auth('sanctum')->user();
        if (!$course->isVisibleFor($user)) {
            throw new ModelNotFoundException('Course not found.');
        }
        return $course->load('author', 'lessons');
    }

    public function update()
    {
        //
    }

    public function destroy()
    {
        //
    }
}
