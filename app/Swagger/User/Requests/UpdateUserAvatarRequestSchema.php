<?php

declare(strict_types=1);

namespace App\Swagger\User\Requests;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'UpdateUserAvatarRequest',
    title: 'Update User Avatar Request',
    description: 'Multipart form data payload for uploading a new avatar via PUT spoofing.',
    required: ['avatar', '_method'],
    properties: [
        new OA\Property(
            property: 'avatar',
            description: 'The avatar image file. Allowed formats: jpg, jpeg, png, webp. Maximum size: 2MB (2048 KB).',
            type: 'string',
            format: 'binary'
        ),
        new OA\Property(
            property: '_method',
            description: 'Method spoofing to handle multipart/form-data in PUT requests.',
            type: 'string',
            default: 'PUT',
            enum: ['PUT'],
            example: 'PUT'
        ),
    ],
    type: 'object'
)]
class UpdateUserAvatarRequestSchema {}
