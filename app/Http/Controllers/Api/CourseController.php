<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Services\CourseService;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(CourseService $service)
    {
//        $service->index();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, CourseService $service)
    {
//        $service->store();
    }

    /**
     * Display the specified resource.
     */
    public function show(Course $course, CourseService $service)
    {
//        $service->show();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Course $course, CourseService $service)
    {
//        $service->update();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Course $course, CourseService $service)
    {
        $service->destroy();
    }
}
