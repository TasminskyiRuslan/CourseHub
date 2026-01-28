<?php

namespace App\Exceptions\Api;

use Exception;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

abstract class ApiException extends Exception
{
    protected array $details = [];
    public function __construct(string $message = "", int $code = HttpResponse::HTTP_BAD_REQUEST, array $details = [])
    {
        parent::__construct($message, $code);
        $this->details = $details;

    }

    public function render($request): JsonResponse
    {
        return response()->error(
            $this->getMessage(),
            $this->details,
            $this->getCode(),
        );
    }
}
