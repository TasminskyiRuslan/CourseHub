<?php

declare(strict_types=1);

namespace App\Enums;

enum UserPermission: string
{
    case TEACHER_PANEL_ACCESS = 'teacher-panel:access';
    case ADMIN_PANEL_ACCESS = 'admin-panel:access';

    case COURSES_CREATE = 'courses:create';
    case COURSES_UPDATE_OWN = 'courses:update-own';
    case COURSES_DELETE_OWN = 'courses:delete-own';
    case COURSES_DELETE_ALL = 'courses:delete-all';
    case COURSES_PUBLISH_OWN = 'courses:publish-own';
    case COURSES_BAN_ALL = 'courses:ban-all';

    case LESSONS_CREATE = 'lessons:create';
    case LESSONS_UPDATE_OWN = 'lessons:update-own';
    case LESSONS_DELETE_OWN = 'lessons:delete-own';
    case LESSONS_DELETE_ALL = 'lessons:delete-all';

    case USERS_UPDATE_ROLES_ALL = 'users:update-roles-all';
    case USERS_DELETE_ALL = 'users:delete-all';
    case USERS_BAN_ALL = 'users:ban-all';
}
