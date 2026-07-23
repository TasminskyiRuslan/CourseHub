<?php

namespace App\Http\Controllers\Api\Course\Student;

use App\Actions\Course\CheckoutCourseAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Course\Student\CheckoutResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use OpenApi\Attributes as OA;

class CheckoutController extends Controller
{
    #[OA\Post(
        path: '/student/courses/{course}/checkout',
        description: 'Check out the specified course for the authenticated user.',
        summary: '[Student] Check out course',
        security: [['sanctum' => []]],
        tags: ['Course'],
        parameters: [
            new OA\Parameter(
                name: 'course',
                description: 'Course identifier (slug).',
                in: 'path',
                required: true,
                schema: new OA\Schema(
                    type: 'string',
                    example: 'math-101'
                )
            )
        ],
        responses: [
            new OA\Response(
                response: SymfonyResponse::HTTP_OK,
                description: 'Course checked out successfully.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/CourseCheckoutResponse'
                        )
                    ]
                )
            ),
            new OA\Response(
                response: SymfonyResponse::HTTP_UNAUTHORIZED,
                description: 'User is unauthenticated.'
            ),
            new OA\Response(
                response: SymfonyResponse::HTTP_FORBIDDEN,
                description: 'User does not have permissions.'
            ),
            new OA\Response(
                response: SymfonyResponse::HTTP_NOT_FOUND,
                description: 'Course not found.'
            ),
            new OA\Response(
                response: SymfonyResponse::HTTP_UNPROCESSABLE_ENTITY,
                description: 'Validation error.'
            )
        ]
    )]
    /**
     * Check out the specified course for the authenticated user.
     *
     * @param Request $request
     * @param CheckoutCourseAction $action
     * @param string $course
     * @return JsonResponse
     */
    public function __invoke(Request $request, CheckoutCourseAction $action, string $course): JsonResponse
    {
        $currentUser = $request->user();

        $result = $action->handle($currentUser, $course);

        return CheckoutResource::make($result)
            ->response()
            ->setStatusCode(SymfonyResponse::HTTP_OK);
    }
}
