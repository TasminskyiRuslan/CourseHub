<?php

declare(strict_types=1);

namespace App\Enums;

enum UserRole: string
{
    case TEACHER = 'teacher';
    case SUPER_ADMIN = 'super-admin';
    case ADMIN = 'admin';
}
