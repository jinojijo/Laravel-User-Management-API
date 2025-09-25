<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        // Default API rate limiting
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip())
                ->response(function (Request $request, array $headers) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Too many requests. Please slow down.',
                        'retry_after' => $headers['Retry-After'] ?? 60
                    ], 429, $headers);
                });
        });

        // Stricter rate limiting for authentication endpoints
        RateLimiter::for('auth', function (Request $request) {
            return [
                Limit::perMinute(5)->by($request->ip())->response(function (Request $request, array $headers) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Too many authentication attempts. Please try again later.',
                        'retry_after' => $headers['Retry-After'] ?? 60
                    ], 429, $headers);
                }),
                Limit::perHour(20)->by($request->ip())
            ];
        });

        // Rate limiting for write operations (create, update, delete)
        RateLimiter::for('writes', function (Request $request) {
            return Limit::perMinute(30)->by($request->user()?->id ?: $request->ip())
                ->response(function (Request $request, array $headers) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Too many write operations. Please slow down.',
                        'retry_after' => $headers['Retry-After'] ?? 60
                    ], 429, $headers);
                });
        });

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }
}
