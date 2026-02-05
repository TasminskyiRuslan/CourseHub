<?php

namespace App\Services\Courses;

use App\Data\Courses\CreateCourseData;
use App\Data\Courses\UpdateCourseData;
use App\Models\Course;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Throwable;

class CourseService
{
    public function __construct()
    {
    }

    public function search(): LengthAwarePaginator
    {
        return QueryBuilder::for(Course::class)
            ->allowedFilters([
                'type',
                AllowedFilter::callback('search', function ($query, $value) {
                    $query->where(function ($q) use ($value) {
                        $q->where('title', 'like', "%$value%")
                            ->orWhere('description', 'like', "%$value%");
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
    public function create(CreateCourseData $data, User $author): Course
    {
        $course = $author->courses()->create($data->toArray());
        return $course->load('author');
    }

    /**
     * @throws Throwable
     */
    public function updateImage(Course $course, UploadedFile $image): Course
    {
        $oldImagePath = $course->image_path;
        $path = $image->store('courses', 'public');

        try {
            DB::transaction(function () use ($course, $path) {
                $course->update(['image_path' => $path]);
            });
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($path);
            throw $exception;
        }

        if ($oldImagePath) {
            Storage::disk('public')->delete($oldImagePath);
        }

        return $course->fresh('author');
    }

    /**
     * @throws Throwable
     */
    public function update(UpdateCourseData $data, Course $course): Course
    {
        $course->update($data->toArray());
        return $course->fresh('author');
    }

    /**
     * @throws Throwable
     */
    public function delete(Course $course): void
    {
        DB::transaction(function () use ($course) {
            $this->deleteImage($course);
            $course->delete();
        });
    }

    public function deleteImage(Course $course): void
    {
        $imagePath = $course->image_path;
        if (!$imagePath) {
            return;
        }

        $course->update(['image_path' => null]);
        Storage::disk('public')->delete($imagePath);
    }

    public function publish(Course $course): void
    {
        $course->publish();
        $course->save();
    }

    public function unpublish(Course $course): void
    {
        $course->unpublish();
        $course->save();
    }
}
