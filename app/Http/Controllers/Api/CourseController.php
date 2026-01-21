<?php

namespace App\Http\Controllers\Api;

use App\DTO\CreateCourseDTO;
use App\DTO\UpdateCourseDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreCourseRequest;
use App\Http\Requests\Api\UpdateCourseRequest;
use App\Http\Resources\CourseResource;
use App\Models\Course;
use App\Services\CourseService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Throwable;

class CourseController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected CourseService $courseService,
    )
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Course::class);
        $result = $this->courseService->search();
        return CourseResource::collection($result);
    }

    /**
     * @throws Throwable
     */
    public function store(StoreCourseRequest $request)
    {
        $this->authorize('create', Course::class);
        $result = $this->courseService->create(CreateCourseDTO::fromRequest($request), $request->user());
        return response()->success(
            'Course created successfully.',
            new CourseResource($result),
            HttpResponse::HTTP_CREATED
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Course $course)
    {
        $this->authorize('view', $course);
        $course->loadMissing(['author']);
        return new CourseResource($course);
    }

    /**
     * Update the specified resource in storage.
     * @throws Throwable
     */
    public function update(UpdateCourseRequest $request, Course $course)
    {
        $this->authorize('update', $course);
        $result = $this->courseService->update(UpdateCourseDTO::fromRequest($request), $course);
        $result->loadMissing(['author']);
        return response()->success(
            'Course updated successfully.',
            new CourseResource($result)
        );
    }

    /**
     * Remove the specified resource from storage.
     * @throws Throwable
     */
    public function destroy(Course $course)
    {
        $this->authorize('delete', $course);
        $this->courseService->delete($course);
        return response()->noContent();
    }
}
