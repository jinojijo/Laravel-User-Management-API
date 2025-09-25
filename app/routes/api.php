<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public authentication routes with stricter rate limiting
Route::prefix('auth')->middleware('throttle:auth')->group(function () {
    Route::post('login', [AuthController::class, 'login'])->name('auth.login');
    Route::post('register', [AuthController::class, 'register'])->name('auth.register');
});

Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
    Route::prefix('auth')->group(function () {
        Route::get('me', [AuthController::class, 'me'])->name('auth.me');
        Route::post('logout', [AuthController::class, 'logout'])->name('auth.logout');
        Route::post('refresh', [AuthController::class, 'refresh'])->name('auth.refresh');
    });

    // User CRUD routes with additional throttling for write operations
    Route::get('users', [UserController::class, 'index'])->name('users.index');
    Route::get('users/{user}', [UserController::class, 'show'])->name('users.show');
    
    Route::middleware('throttle:writes')->group(function () {
        Route::post('users', [UserController::class, 'store'])->name('users.store');
        Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::patch('users/{user}', [UserController::class, 'update'])->name('users.patch');
        Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });
});


Route::get('/health', function () {
    return response()->json([
        'status' => 'success',
        'message' => 'API is healthy',
        'timestamp' => now()->toISOString(),
        'version' => '1.0'
    ]);
})->name('health');

// // Database connection check endpoint
// Route::get('/db-status', function () {
//     try {
//         // Test database connection
//         $connection = DB::connection();
//         $connection->getPdo();
        
//         // Test if users table exists and get count
//         $userCount = DB::table('users')->count();
        
//         // Check if migrations have been run
//         $migrations = DB::table('migrations')->count();
        
//         return response()->json([
//             'status' => 'success',
//             'message' => 'Database connection successful',
//             'data' => [
//                 'connection' => 'OK',
//                 'database_name' => $connection->getDatabaseName(),
//                 'driver' => $connection->getDriverName(),
//                 'users_table_exists' => true,
//                 'users_count' => $userCount,
//                 'migrations_count' => $migrations,
//             ],
//             'timestamp' => now()->toISOString()
//         ]);
        
//     } catch (\Exception $e) {
//         // Check what type of error we have
//         $errorType = 'unknown';
//         $suggestions = [];
        
//         if (strpos($e->getMessage(), 'Connection refused') !== false) {
//             $errorType = 'connection_refused';
//             $suggestions[] = 'Check if database server is running';
//             $suggestions[] = 'Verify database host and port in .env file';
//         } elseif (strpos($e->getMessage(), 'Access denied') !== false) {
//             $errorType = 'access_denied';
//             $suggestions[] = 'Check database username and password in .env file';
//             $suggestions[] = 'Verify database user has proper permissions';
//         } elseif (strpos($e->getMessage(), "doesn't exist") !== false || strpos($e->getMessage(), 'Table') !== false) {
//             $errorType = 'table_missing';
//             $suggestions[] = 'Run migrations: php artisan migrate';
//             $suggestions[] = 'Check if database exists';
//         } elseif (strpos($e->getMessage(), 'Unknown database') !== false) {
//             $errorType = 'database_not_found';
//             $suggestions[] = 'Create the database specified in .env file';
//             $suggestions[] = 'Check DB_DATABASE value in .env file';
//         }
        
//         return response()->json([
//             'status' => 'error',
//             'message' => 'Database connection failed',
//             'error' => [
//                 'type' => $errorType,
//                 'message' => $e->getMessage(),
//                 'suggestions' => $suggestions,
//             ],
//             'env_info' => [
//                 'DB_CONNECTION' => env('DB_CONNECTION'),
//                 'DB_HOST' => env('DB_HOST'),
//                 'DB_PORT' => env('DB_PORT'),
//                 'DB_DATABASE' => env('DB_DATABASE'),
//                 'DB_USERNAME' => env('DB_USERNAME'),
//                 'DB_PASSWORD' => env('DB_PASSWORD') ? '***SET***' : 'NOT_SET',
//             ],
//             'timestamp' => now()->toISOString()
//         ], 500);
//     }
// })->name('db-status');

Route::fallback(function () {
    return response()->json([
        'status' => 'error',
        'message' => 'Route not found',
        'available_endpoints' => [
            'POST /api/auth/login',
            'POST /api/auth/register',
            'POST /api/auth/logout',
            'GET /api/auth/me',
            'GET /api/users',
            'POST /api/users',
            'GET /api/users/{id}',
            'PUT /api/users/{id}',
            'DELETE /api/users/{id}',
            'GET /api/health',
            // 'GET /api/db-status'
        ]
    ], 404);
});

