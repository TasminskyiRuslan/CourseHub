<?php

declare(strict_types=1);

namespace App\Queries\Course\Admin;

use App\Models\Course;
use App\Queries\BaseQuery;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

readonly class GetCoursesQuery extends BaseQuery
{
    /**
     * Retrieve a paginated list of all courses by administrator.
     */
    public function handle(Request $request): LengthAwarePaginator
    {
        return $this->query($request)
            ->paginate(config('pagination.courses_per_page'))
            ->withQueryString();
    }

    /**
     * Build query builder with allowed filters and sorting.
     */
    protected function query(Request $request): QueryBuilder
    {
        return QueryBuilder::for(Course::withTrashed(), $request)
            ->with(['author' => function (Builder $query): void {
                $query->withTrashed()
                    ->with(['roles'])
                    ->withCount(['courses' => fn (Builder $q): Builder => $q->withTrashed()]);
            }])
            ->withCount(['lessons' => fn (Builder $query): Builder => $query->withTrashed()])
            ->allowedFilters([
                'type',
                AllowedFilter::callback('search', function (Builder $query, mixed $value): void {
                    $query->where(function (Builder $q) use ($value): void {
                        $q->where('title', 'like', "%{$value}%")
                            ->orWhere('description', 'like', "%{$value}%");
                    });
                }),
                AllowedFilter::callback('author', function (Builder $query, mixed $value): void {
                    $query->whereHas('author', function (Builder $q) use ($value): void {
                        $q->withTrashed()
                            ->where('slug', $value);
                    });
                }),
                AllowedFilter::callback('banned', function (Builder $query, mixed $value): void {
                    filter_var($value, FILTER_VALIDATE_BOOLEAN)
                        ? $query->whereNotNull('banned_at')
                        : $query->whereNull('banned_at');
                }),
                AllowedFilter::callback('published', function (Builder $query, mixed $value): void {
                    filter_var($value, FILTER_VALIDATE_BOOLEAN)
                        ? $query->whereNotNull('published_at')
                        : $query->whereNull('published_at');
                }),
                AllowedFilter::trashed(),
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
