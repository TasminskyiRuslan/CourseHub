<?php

namespace App\Enums;

enum UserRole: string
{
    case TEACHER = 'teacher';
    case SUPER_ADMIN = 'super-admin';
    case ADMIN = 'admin';
}
