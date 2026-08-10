<?php

declare(strict_types=1);

namespace App\Queries\Lesson\Admin;

use App\Models\Course;
use App\Queries\BaseQuery;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

readonly class GetLessonsQuery extends BaseQuery
{
    /**
     * Retrieve a paginated list of lessons for the specified course with conditional caching.
     */
    public function handle(Request $request, Course $course): LengthAwarePaginator
    {
        return $this->query($request, $course)
            ->paginate(config('pagination.lessons_per_page'))
            ->withQueryString();
    }

    /**
     * Build query builder with allowed filters and sorting.
     */
    protected function query(Request $request, Course $course): QueryBuilder
    {
        return QueryBuilder::for($course->lessons()->withTrashed(), $request)
            ->with(['lessonable' => fn (MorphTo $morphTo): MorphTo => $morphTo->withTrashed()])
            ->allowedFilters([
                AllowedFilter::callback('search', function (Builder $query, mixed $value): void {
                    $query->where('title', 'like', "%$value%");
                }),
                AllowedFilter::trashed(),
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
