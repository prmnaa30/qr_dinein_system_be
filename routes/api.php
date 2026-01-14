<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\TableController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

// ? Public routes | Auth
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// ? Public routes | Product, Categories, Order
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{product}', [ProductController::class, 'show']);
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{category}', [CategoryController::class, 'show']);
Route::post('/orders', [OrderController::class, 'store']);
Route::get('/tables/resolve/{uuid}', [TableController::class, 'resolveUuid']);

Route::middleware('auth:sanctum')->group(function () {
    // ? User data and Logout
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::middleware(['role:admin'])->prefix('admin')->group(function () {
        // ? Products management
        Route::apiResource('/products', ProductController::class)
            ->except(['index', 'show']);

        // ? Categories management
        Route::apiResource('/categories', CategoryController::class)
            ->except(['index', 'show']);

        // ? Tables management
        Route::apiResource('/tables', TableController::class)
            ->except('show', 'update');
        Route::get('/tables/{id}/qr', [TableController::class, 'downloadQr']);

        // ? User management
        Route::apiResource('/users', UserController::class);
    });

    // ? Get Kitchen Orders
    Route::get('/orders/kitchen', [OrderController::class, 'getKitchenOrders'])
        ->middleware(['role:admin,kitchen']);

    // ? Get Cashier Orders
    Route::get('/orders/cashier', [OrderController::class, 'getCashierOrders'])
        ->middleware(['role:admin,cashier']);

    // ? Patch Orders Data (usually order's status)
    Route::patch('/orders/{id}/status', [OrderController::class, 'updateStatus'])
        ->middleware(['role:admin,cashier,kitchen']);
});
