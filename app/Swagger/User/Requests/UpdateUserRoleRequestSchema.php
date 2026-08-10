<?php

declare(strict_types=1);

namespace App\Swagger\User\Requests;

use App\Enums\UserRole;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'UpdateUserRoleRequest',
    title: 'Update User Role Request',
    description: 'Payload for updating a user role.',
    required: ['roles'],
    properties: [
        new OA\Property(
            property: 'roles',
            description: 'List of assigned roles.',
            type: 'array',
            items: new OA\Items(
                type: 'string',
                enum: [
                    UserRole::TEACHER->value,
                ]
            ),
            example: [UserRole::TEACHER->value]
        ),
    ],
    type: 'object'
)]
class UpdateUserRoleRequestSchema {}
