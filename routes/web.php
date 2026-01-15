<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make sure something
| is here so the application doesn't fail.
|
*/

// Simple route to test the application is running
Route::get('/', function () {
    return response()->json(['message' => 'QR Dine-in System API is running']);
});

// Add a test route to verify cookie handling
Route::get('/test-cookie', function (\Illuminate\Http\Request $request) {
    $hasCookie = $request->hasCookie('auth_token');
    $cookieValue = $request->cookie('auth_token');

    // Check if Authorization header exists
    $hasAuthHeader = $request->header('authorization');

    return response()->json([
        'has_auth_cookie' => $hasCookie,
        'cookie_value' => $hasCookie ? substr($cookieValue, 0, 20) . '...' : null,
        'has_auth_header' => $hasAuthHeader,
        'auth_header' => $hasAuthHeader ? $request->header('authorization') : null,
    ]);
});
