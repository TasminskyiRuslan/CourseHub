<?php

namespace App\Queries\Course\Admin;

use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class GetCoursesQuery
{
    /**
     * Retrieve a paginated list of all courses for admins.
     *
     * @param Request $request
     * @return LengthAwarePaginator
     */
    public function handle(Request $request): LengthAwarePaginator
    {
        return $this->query($request)
            ->paginate(config('pagination.courses_per_page'))
            ->withQueryString();
    }

    /**
     * Build query builder with allowed filters and sorting.
     *
     * @param Request $request
     * @return QueryBuilder
     */
    protected function query(Request $request): QueryBuilder
    {
        return QueryBuilder::for(Course::class, $request)
            ->with(['author' => function ($query) {
                $query->with('roles')->withCount(['courses' => fn ($q) => $q->withTrashed()])->withTrashed();
            }])
            ->withCount(['lessons' => fn ($query) => $query->withTrashed()])
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
                        $q->withTrashed()->where('slug', $value);
                    });
                }),
                AllowedFilter::callback('banned', function ($query, $value) {
                    filter_var($value, FILTER_VALIDATE_BOOLEAN) ? $query->whereNotNull('banned_at') : $query->whereNull('banned_at');
                }),
                AllowedFilter::callback('published', function ($query, $value) {
                    filter_var($value, FILTER_VALIDATE_BOOLEAN) ? $query->whereNotNull('published_at') : $query->whereNull('published_at');
                }),
                AllowedFilter::trashed()
            ])
            ->allowedSorts([
                'title',
                'price',
                'created_at',
                'published_at',
                'banned_at',
                'deleted_at',
                'lessons_count',
            ])
            ->defaultSort('-created_at');
    }
}
