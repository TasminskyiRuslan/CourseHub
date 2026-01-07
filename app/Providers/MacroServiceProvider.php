<?php

namespace App\Providers;

use Illuminate\Support\Facades\Response;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class MacroServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */

    public function boot(): void
    {
        Response::macro('success', function (string $message = '', mixed $data = [], int $status = HttpResponse::HTTP_OK) {
            return response()->json([
                'status' => 'success',
                'message' => $message,
                'data' => $data,
            ], $status);
        });

        Response::macro('error', function (string $message = '', array $details = [], int $status = HttpResponse::HTTP_BAD_REQUEST) {
            return response()->json([
                'status' => 'error',
                'message' => $message,
                'details' => $details,
            ], $status);
        });
    }

}
