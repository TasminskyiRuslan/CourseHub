<?php

namespace App\Enums;

enum UserPermission: string
{
    // Course
    case COURSE_VIEW_ANY_UNPUBLISHED = 'course:view-any-unpublished';
    case COURSE_CREATE = 'course:create';
    case COURSE_DELETE_ANY = 'course:delete-any';
    case COURSE_UNPUBLISH_ANY = 'course:unpublish-any';

    // Lesson
    case LESSON_CREATE = 'lesson:create';
    case LESSON_DELETE_ANY = 'lesson:delete-any';

    // User
    case USER_VIEW_ANY = 'user:view-any';
    case USER_DELETE_ANY = 'user:delete-any';
    case USER_ROLE_EDIT_ANY = 'user:role-edit-any';
    case USER_BAN_ANY = 'user:ban-any';
    case USER_UNBAN_ANY = 'user:unban-any';
}
