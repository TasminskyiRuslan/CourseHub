<?php

namespace App\Queries\Course\Student;

use App\Models\Course;
use App\Models\User;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;

readonly class GetCoursesQuery
{
    /**
     * Retrieve a paginated list of student's enrolled courses.
     *
     * @param Request $request
     * @param User $user
     * @return LengthAwarePaginator
     */
    public function handle(Request $request, User $user): LengthAwarePaginator
    {
        return $this->query($request, $user)
            ->paginate(config('pagination.courses_per_page'))
            ->withQueryString();
    }

    /**
     * Build query builder with allowed filters and sorting.
     *
     * @param Request $request
     * @param User $user
     * @return QueryBuilder
     */
    protected function query(Request $request, User $user): QueryBuilder
    {
        return QueryBuilder::for($user->enrolledCourses()->active(), $request)
            ->with(['author' => function (Builder $query) {
                $query->with(['roles'])
                    ->withCount(['courses' => fn (Builder $q) => $q->active()]);
            }])
            ->withCount(['lessons'])
            ->allowedFilters([
                'type',
                AllowedFilter::callback('search', function (Builder $query, mixed $value) {
                    $query->where(function (Builder $q) use ($value) {
                        $q->where('title', 'like', "%{$value}%")
                            ->orWhere('description', 'like', "%{$value}%");
                    });
                }),
                AllowedFilter::callback('author', function (Builder $query, mixed $value) {
                    $query->whereRelation('author', 'slug', $value);
                }),
            ])
            ->allowedSorts([
                'title',
                'price',
                'published_at',
                'lessons_count',
                AllowedSort::field('enrolled_at', 'course_user.enrolled_at'),
            ])
            ->defaultSort('-course_user.enrolled_at');
    }
}
