<?php

namespace App\Http\Controllers\Api;

use App\DTO\LessonDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreLessonRequest;
use App\Http\Requests\Api\UpdateLessonRequest;
use App\Http\Resources\LessonResource;
use App\Models\Course;
use App\Models\Lesson;
use App\Services\LessonService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Throwable;

class LessonController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected LessonService $lessonService,
    )
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Course $course)
    {
        $this->authorize('viewAny', [Lesson::class, $course]);
        $result = $this->lessonService->search($course);
        return LessonResource::collection($result);
    }

    /**
     * Store a newly created resource in storage.
     * @throws Throwable
     */
    public function store(StoreLessonRequest $request, Course $course)
    {
        $this->authorize('create', [Lesson::class, $course]);
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
    public function show(Course $course, Lesson $lesson)
    {
        $this->authorize('view', $lesson);
        return new LessonResource($lesson->loadMissing('lessonable'));
    }

    /**
     * Update the specified resource in storage.
     * @throws Throwable
     */
    public function update(UpdateLessonRequest $request, Course $course, Lesson $lesson)
    {
        $this->authorize('update', $lesson);
        $result = $this->lessonService->update($lesson, LessonDTO::fromRequest($request));
        return response()->success(
            'Lesson updated successfully',
            new LessonResource($result)
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Lesson $lesson)
    {
        $this->authorize('delete', $lesson);
        //
    }
}
