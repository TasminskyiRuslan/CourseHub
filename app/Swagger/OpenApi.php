<?php

namespace App\Swagger;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: "1.0.0",
    description: "Complete API documentation for CourseHub platform",
    title: "CourseHub API",
    contact: new OA\Contact(
        name: "CourseHub Support",
        email: "admin@coursehub.com"
    )
)]
#[OA\Server(
    url: "http://coursehub.local/api",
    description: "Local development server"
)]
#[OA\SecurityScheme(
    securityScheme: "sanctum",
    type: "http",
    description: "Use your Sanctum token to authenticate requests",
    bearerFormat: "API Token",
    scheme: "bearer"
)]
class OpenApi
{
}
