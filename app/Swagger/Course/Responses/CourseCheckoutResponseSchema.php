<?php

namespace App\Swagger\Course\Responses;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'CourseCheckoutResponse',
    title: 'Course Checkout Response',
    description: 'Course checkout result data.',
    required: ['status', 'is_enrolled', 'course_id', 'checkout_url'],
    properties: [
        new OA\Property(
            property: 'status',
            description: 'Status of the checkout process.',
            type: 'string',
            example: 'enrolled',
        ),
        new OA\Property(
            property: 'is_enrolled',
            description: 'Indicates if the user was enrolled in the course.',
            type: 'boolean',
            example: true
        ),
        new OA\Property(
            property: 'course_id',
            description: 'Course identifier.',
            type: 'integer',
            example: 42
        ),
        new OA\Property(
            property: 'checkout_url',
            description: 'Redirect URL for payment gateways.',
            type: 'string',
            format: 'uri',
            example: 'https://checkout.stripe.com/c/pay/cs_test_123',
            nullable: true
        )
    ],
    type: 'object'
)]
class CourseCheckoutResponseSchema
{

}
