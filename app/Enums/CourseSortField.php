<?php

namespace App\Enums;

enum CourseSortField: string
{
    case CREATED_AT = 'created_at';
    case PRICE = 'price';
    case TITLE = 'title';
}
