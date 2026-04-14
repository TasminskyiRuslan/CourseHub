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
    url: "http://localhost:8080/api",
    description: "Local development server"
)]
#[OA\SecurityScheme(
    securityScheme: "sanctum",
    type: "http",
    description: "Enter token in format: Bearer {token}",
    name: "Authorization",
    in: "header",
    bearerFormat: "JWT",
    scheme: "bearer"
)]
class OpenApi
{
}
