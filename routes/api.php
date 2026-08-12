<?php

use App\Http\Controllers\Api\V1\Admin\ActivityLogController;
use App\Http\Controllers\Api\V1\Admin\CategoryController;
use App\Http\Controllers\Api\V1\Admin\DashboardController;
use App\Http\Controllers\Api\V1\Admin\ProductController;
use App\Http\Controllers\Api\V1\Admin\RestaurantSettingController;
use App\Http\Controllers\Api\V1\Admin\TableController;
use App\Http\Controllers\Api\V1\Admin\UserController;
use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\CartController;
use App\Http\Controllers\Api\V1\Cashier\PaymentController;
use App\Http\Controllers\Api\V1\Kitchen\KitchenController;
use App\Http\Controllers\Api\V1\MenuController;
use App\Http\Controllers\Api\V1\OrderController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // Public: authentication
    Route::post('/auth/login', [AuthController::class, 'login']);

    // Public: customer QR ordering journey
    Route::get('/menu/{qrToken}', [MenuController::class, 'show']);
    Route::post('/cart/validate', [CartController::class, 'validateCart']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders/track/{orderNumber}', [OrderController::class, 'track']);

    // Authenticated (any staff role)
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me', [AuthController::class, 'me']);

        // Admin + Kitchen + Cashier: read orders
        Route::middleware('role:admin,kitchen,cashier')->group(function () {
            Route::get('/orders', [OrderController::class, 'index']);
            Route::get('/orders/{order}', [OrderController::class, 'show']);
        });

        // Admin + Kitchen: kitchen dashboard + order status transitions
        Route::middleware('role:admin,kitchen')->group(function () {
            Route::get('/kitchen/dashboard', [KitchenController::class, 'dashboard']);
            Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus']);
        });

        // Admin + Cashier: payments
        Route::middleware('role:admin,cashier')->group(function () {
            Route::get('/cashier/payments', [PaymentController::class, 'index']);
            Route::patch('/cashier/payments/{order}/pay', [PaymentController::class, 'pay']);
        });

        // Admin only
        Route::middleware('role:admin')->prefix('admin')->group(function () {
            Route::get('/dashboard', [DashboardController::class, 'index']);
            Route::get('/activity-logs', [ActivityLogController::class, 'index']);

            Route::get('/settings', [RestaurantSettingController::class, 'show']);
            Route::put('/settings', [RestaurantSettingController::class, 'update']);

            Route::apiResource('tables', TableController::class);
            Route::post('/tables/{table}/regenerate-qr', [TableController::class, 'regenerateQr']);

            Route::apiResource('categories', CategoryController::class);
            Route::apiResource('products', ProductController::class);
            Route::apiResource('users', UserController::class);
        });
    });
});
