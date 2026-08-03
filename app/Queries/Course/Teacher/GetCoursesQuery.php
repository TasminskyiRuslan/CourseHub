<?php

namespace App\Queries\Course\Teacher;

use App\Models\Course;
use App\Models\User;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

readonly class GetCoursesQuery
{
    /**
     * Retrieve a paginated list of teacher's courses.
     *
     * @param Request $request
     * @param User $teacher
     * @return LengthAwarePaginator
     */
    public function handle(Request $request, User $teacher): LengthAwarePaginator
    {
        return $this->query($request, $teacher)
            ->paginate(config('pagination.courses_per_page'))
            ->withQueryString();
    }

    /**
     * Build query builder with allowed filters and sorting.
     *
     * @param Request $request
     * @param User $teacher
     * @return QueryBuilder
     */
    protected function query(Request $request, User $teacher): QueryBuilder
    {
        return QueryBuilder::for($teacher->courses(), $request)
            ->withCount(['lessons'])
            ->allowedFilters([
                'type',
                AllowedFilter::callback('search', function (Builder $query, mixed $value) {
                    $query->where(function (Builder $q) use ($value) {
                        $q->where('title', 'like', "%{$value}%")
                            ->orWhere('description', 'like', "%{$value}%");
                    });
                }),
                AllowedFilter::callback('banned', function (Builder $query, mixed $value) {
                    filter_var($value, FILTER_VALIDATE_BOOLEAN)
                        ? $query->whereNotNull('banned_at')
                        : $query->whereNull('banned_at');
                }),
                AllowedFilter::callback('published', function (Builder $query, mixed $value) {
                    filter_var($value, FILTER_VALIDATE_BOOLEAN)
                        ? $query->whereNotNull('published_at')
                        : $query->whereNull('published_at');
                })
            ])
            ->allowedSorts([
                'title',
                'price',
                'created_at',
                'published_at',
                'lessons_count',
            ])
            ->defaultSort('-created_at');
    }
}
