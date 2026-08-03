<?php

namespace App\Queries\Lesson\Student;

use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

readonly class GetLessonsQuery
{
    /**
     * Retrieve a paginated list of lessons for the specified student's enrolled course.
     *
     * @param Request $request
     * @param User $student
     * @param string $courseSlug
     * @return LengthAwarePaginator
     * @throws ModelNotFoundException
     */
    public function handle(Request $request, User $student, string $courseSlug): LengthAwarePaginator
    {
        $course = $student->enrolledCourses()
            ->active()
            ->where('slug', $courseSlug)
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
        return QueryBuilder::for($course->lessons(), $request)
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
