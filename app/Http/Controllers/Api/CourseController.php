<?php

namespace App\Http\Controllers\Api;

use App\DTO\CourseDTO;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Services\CourseService;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function __construct(
        protected CourseService $courseService,
    )
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
//        $this->courseService->index();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
//        $this->courseService->store(CourseDTO::fromRequest($request));
    }

    /**
     * Display the specified resource.
     */
    public function show(Course $course)
    {
//        $this->courseService->show();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Course $course)
    {
//        $this->courseService->update();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Course $course)
    {
//        $this->courseService->destroy();
    }
}
