<?php

namespace App\Http\Controllers\Api;

use App\DTO\CourseDTO;
use App\DTO\CourseFilterDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CourseListRequest;
use App\Http\Requests\Api\StoreCourseRequest;
use App\Http\Resources\CourseResource;
use App\Models\Course;
use App\Services\CourseService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

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
    public function index(CourseListRequest $request)
    {
        $result = $this->courseService->find(CourseFilterDTO::fromRequest($request));
        return CourseResource::collection($result);
    }

    /**
     * @throws \Throwable
     */
    public function store(StoreCourseRequest $request)
    {
        $result = $this->courseService->store(CourseDTO::fromRequest($request), $request->user());
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
//        $this->authorize('view', Course::class);
        $this->courseService->findOne($course);
        return new CourseResource($course);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Course $course)
    {
        $this->authorize('update', Course::class);
//        $this->courseService->update();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Course $course)
    {
        $this->authorize('delete', Course::class);
//        $this->courseService->destroy();
    }
}
