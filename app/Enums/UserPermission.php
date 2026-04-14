<?php

namespace App\Enums;

enum UserPermission: string
{
    // Course
    case COURSE_CREATE = 'course.create';
    case COURSE_UPDATE = 'course.update';
    case COURSE_DELETE = 'course.delete';
    case COURSE_PUBLISH = 'course.publish';
    case COURSE_UNPUBLISH = 'course.unpublish';
    case COURSE_SHOW_UNPUBLISHED = 'course.show.unpublished';
    case COURSE_DELETE_ANY = 'course.delete.any';
    case COURSE_UNPUBLISH_ANY = 'course.unpublish.any';

    // Lesson
    case LESSON_CREATE = 'lesson.create';
    case LESSON_UPDATE = 'lesson.update';
    case LESSON_DELETE = 'lesson.delete';
    case LESSON_DELETE_ANY = 'lesson.delete.any';

}
