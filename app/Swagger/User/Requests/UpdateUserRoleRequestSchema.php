<?php

namespace App\Swagger\User\Requests;

use App\Enums\UserRole;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'UpdateUserRoleRequest',
    title: 'Update User Role Request',
    description: 'Request payload for updating of the specified user.',
    required: ['role'],
    properties: [
        new OA\Property(
            property: 'role',
            description: 'Role of the user.',
            type: 'string',
            enum: [
                UserRole::STUDENT->value,
                UserRole::TEACHER->value,
                UserRole::ADMIN->value,
            ],
            example: UserRole::TEACHER->value
        )
    ],
    type: 'object'
)]
class UpdateUserRoleRequestSchema
{

}
