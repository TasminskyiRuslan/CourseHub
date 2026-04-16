<?php

namespace App\Actions\Course;

use App\Data\Course\Requests\CreateCourseData;
use App\Models\Course;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Throwable;

class CreateCourseAction
{
    /**
     * Create a new course.
     *
     * @param CreateCourseData $courseData
     * @param User $author
     * @return Course
     * @throws Throwable
     */
    public function handle(CreateCourseData $courseData, User $author): Course
    {
        return DB::transaction(function () use ($courseData, $author) {
            return $author->courses()->create($courseData->all());
        });
    }
}
