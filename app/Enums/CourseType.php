<?php

namespace App\Enums;

enum CourseType: string
{
    case OFFLINE = 'offline';
    case ONLINE = 'online';
    case VIDEO = 'video';
}
