<?php

namespace App\Queries\Course\Student;

use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;

readonly class GetCoursesQuery
{
    /**
     * Retrieve a paginated list of student's courses.
     *
     * @param Request $request
     * @param User $student
     * @return LengthAwarePaginator
     */
    public function handle(Request $request, User $student): LengthAwarePaginator
    {
        return $this->query($request, $student)
            ->paginate(config('pagination.courses_per_page'))
            ->withQueryString();
    }

    /**
     * Build query builder with allowed filters and sorting.
     *
     * @param Request $request
     * @param User $student
     * @return QueryBuilder
     */
    protected function query(Request $request, User $student): QueryBuilder
    {
        return QueryBuilder::for($student->enrolledCourses(), $request)
            ->active()
            ->with(['author' => function ($query) {
                $query->with(['roles'])->withCount(['courses' => fn ($q) => $q->active()]);
            }])
            ->withCount(['lessons'])
            ->allowedFilters([
                'type',
                AllowedFilter::callback('search', function ($query, $value) {
                    $query->where(function ($q) use ($value) {
                        $q->where('title', 'like', "%{$value}%")
                            ->orWhere('description', 'like', "%{$value}%");
                    });
                }),
                AllowedFilter::callback('author', function ($query, $value) {
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
