<?php

namespace App\Http\Controllers\Api;

use App\DTO\CourseFilterDTO;
use App\DTO\LessonDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CourseListRequest;
use App\Http\Requests\Api\StoreLessonRequest;
use App\Http\Resources\LessonResource;
use App\Models\Course;
use App\Models\Lesson;
use App\Services\LessonService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Throwable;

class LessonController extends Controller
{
    public function __construct(
        protected LessonService $lessonService,
    )
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(CourseListRequest $request, Course $course)
    {
        $result = $this->lessonService->search(CourseFilterDTO::fromRequest($request), $course);
        return LessonResource::collection($result);
    }

    /**
     * Store a newly created resource in storage.
     * @throws Throwable
     */
    public function store(StoreLessonRequest $request, Course $course)
    {
        $result = $this->lessonService->create($course, LessonDTO::fromRequest($request));

        return response()->success(
            'Lesson created successfully',
            new LessonResource($result),
            HttpResponse::HTTP_CREATED
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Lesson $lesson)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Lesson $lesson)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Lesson $lesson)
    {
        //
    }
}
