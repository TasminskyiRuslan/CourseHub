<?php

namespace App\Http\Resources\Api\Course\Student;

use App\Data\Course\Results\CheckoutResultData;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read CheckoutResultData $resource
 */
class CheckoutResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'status' => $this->resource->status->value,
            'is_enrolled' => $this->resource->status->isEnrolled(),
            'course_id' => $this->resource->courseId,
            'checkout_url' => $this->resource->checkoutUrl,
        ];
    }
}
