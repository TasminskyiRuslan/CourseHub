<?php

namespace App\Enums;

enum UserPermission: string
{
    // Course
    case COURSE_VIEW_UNPUBLISHED = 'course:view-unpublished';
    case COURSE_CREATE = 'course:create';
    case COURSE_EDIT_OWN = 'course:edit-own';
    case COURSE_DELETE_OWN = 'course:delete-own';
    case COURSE_DELETE_ANY = 'course:delete-any';
    case COURSE_PUBLISH_OWN = 'course:publish-own';
    case COURSE_UNPUBLISH_OWN = 'course:unpublish-own';
    case COURSE_UNPUBLISH_ANY = 'course:unpublish-any';

    // Lesson
    case LESSON_CREATE = 'lesson:create';
    case LESSON_EDIT_OWN = 'lesson:edit-own';
    case LESSON_DELETE_OWN = 'lesson:delete-own';
    case LESSON_DELETE_ANY = 'lesson:delete-any';

}
