<?php

namespace App\Queries\Lesson\Teacher;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

readonly class GetLessonsQuery
{
    /**
     * Retrieve a paginated list of lessons for the specified teacher's course.
     *
     * @param Request $request
     * @param string $slug
     * @param User $teacher
     * @return LengthAwarePaginator
     */
    public function handle(Request $request, string $slug, User $teacher): LengthAwarePaginator
    {
        $course = Course::query()
            ->whereBelongsTo($teacher, 'author')
            ->where('slug', $slug)
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
            ->with(['lessonable'])
            ->allowedFilters([
                AllowedFilter::callback('search', function ($query, $value) {
                    $query->where('title', 'like', "%$value%");
                }),
            ])
            ->allowedSorts([
                'title',
                'position',
                'created_at',
            ])
            ->defaultSort('position');
    }
}
