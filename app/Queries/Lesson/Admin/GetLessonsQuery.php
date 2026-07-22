<?php

namespace App\Queries\Lesson\Admin;

use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

readonly class GetLessonsQuery
{
    /**
     * Retrieve a paginated list of lessons for the specified course with conditional caching.
     *
     * @param Request $request
     * @param string $courseSlug
     * @return LengthAwarePaginator
     * @throws ModelNotFoundException
     */
    public function handle(Request $request, string $courseSlug): LengthAwarePaginator
    {
        $course = Course::query()
            ->where('slug', $courseSlug)
            ->withTrashed()
            ->firstOrFail();

        return $this->query($request, $course)
            ->paginate(config('pagination.lessons_per_page'))
            ->withQueryString();
    }

    /**
     * Build query builder with allowed filters and sorting.
     *
     * @param Request $request
     * @param Course $course
     * @return QueryBuilder
     */
    protected function query(Request $request, Course $course): QueryBuilder
    {
        return QueryBuilder::for(Lesson::class, $request)
            ->whereBelongsTo($course, 'course')
            ->with(['lessonable' => fn($morphTo) => $morphTo->withTrashed()])
            ->withTrashed()
            ->allowedFilters([
                AllowedFilter::callback('search', function ($query, $value) {
                    $query->where('title', 'like', "%$value%");
                }),
                AllowedFilter::trashed()
            ])
            ->allowedSorts([
                'title',
                'position',
                'created_at',
                'deleted_at',
            ])
            ->defaultSort('position');
    }
}
