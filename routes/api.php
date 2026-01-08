<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| MOCK API ROUTES (QR Dine-in System)
|--------------------------------------------------------------------------
| Gunakan ini agar tim Frontend bisa kerja paralel.
| Data bersifat statis (Hardcoded) & Stateless (Reset saat refresh).
*/

// ============================================================================
// 1. AUTHENTICATION (Simulasi Login untuk Admin, Kitchen, Cashier)
// ============================================================================

Route::post('/login', function (Request $request) {
    $email = $request->input('email');

    // Default Role Simulations
    $role = 'cashier';
    if (str_contains($email, 'admin')) {
        $role = 'admin';
    }
    if (str_contains($email, 'kitchen')) {
        $role = 'kitchen';
    }

    return response()->json([
        'message' => 'Login (MOCK) berhasil',
        'access_token' => 'mock_token_xyz_123_balibeach',
        'token_type' => 'Bearer',
        'user' => [
            'id' => 1,
            'name' => 'User ' . ucfirst($role),
            'email' => $email,
            'role' => $role, // Penting buat Frontend redirect page
        ]
    ]);
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    // Simulasi return user profile
    return response()->json([
        'id' => 1,
        'name' => 'Mock User',
        'email' => 'admin@kopi.com',
        'role' => 'admin'
    ]);
});


// ============================================================================
// 2. PUBLIC / CUSTOMER (Scan QR & Order Menu)
// ============================================================================

// A. Get Categories (Untuk Filter Menu)
Route::get('/categories', function () {
    return response()->json([
        'data' => [
            ['id' => 1, 'name' => 'Coffee'],
            ['id' => 2, 'name' => 'Non-Coffee'],
            ['id' => 3, 'name' => 'Main Course'],
            ['id' => 4, 'name' => 'Snack'],
        ]
    ]);
});

// B. Get Products (Daftar Menu)
Route::get('/products', function () {
    return response()->json([
        'data' => [
            [
                'id' => 101,
                'category_id' => 1,
                'name' => 'Kopi Susu Gula Aren',
                'description' => 'Espresso dengan susu fresh milk dan gula aren asli.',
                'price' => 18000,
                'image' => 'https://placehold.co/400x300/7a5c48/white?text=Kopi+Susu',
                'is_available' => true,
            ],
            [
                'id' => 102,
                'category_id' => 1,
                'name' => 'Americano',
                'description' => 'Double shot espresso dengan air panas.',
                'price' => 15000,
                'image' => 'https://placehold.co/400x300/3e2723/white?text=Americano',
                'is_available' => true,
            ],
            [
                'id' => 201,
                'category_id' => 3,
                'name' => 'Nasi Goreng Spesial',
                'description' => 'Nasi goreng dengan telor mata sapi dan kerupuk.',
                'price' => 25000,
                'image' => 'https://placehold.co/400x300/ff9800/white?text=Nasgor',
                'is_available' => true,
            ],
            [
                'id' => 401,
                'category_id' => 4,
                'name' => 'Croissant Butter',
                'description' => 'Roti sabit khas perancis.',
                'price' => 22000,
                'image' => 'https://placehold.co/400x300/e6c17a/3e2723?text=Croissant',
                'is_available' => false, // Contoh Sold Out
            ]
        ]
    ]);
});

// C. Checkout / Submit Order
// Frontend mengirim: { table_id: 1, items: [{product_id: 101, qty: 1}] }
Route::post('/orders', function () {
    return response()->json([
        'message' => 'Order created successfully',
        'order_id' => 888,
        'total_amount' => 43000,
        // Ini token PENTING buat munculin popup Midtrans di Frontend
        'snap_token' => 'mock_snap_token_' . time(),
        'payment_status' => 'unpaid'
    ], 201);
});


// ============================================================================
// 3. MIDTRANS WEBHOOK (Simulasi)
// ============================================================================

Route::post('/webhooks/midtrans', function () {
    // Ceritanya Midtrans ngirim notif kalau user sudah bayar
    return response()->json(['status' => 'success', 'message' => 'Order updated to Paid']);
});


// ============================================================================
// 4. KITCHEN DISPLAY SYSTEM (KDS)
// ============================================================================

// A. List Pesanan Masuk (Hanya yang PAID & BELUM SELESAI)
Route::get('/orders/kitchen', function () {
    return response()->json([
        'data' => [
            [
                'id' => 881,
                'table_number' => 'Meja 03',
                'status' => 'paid', // Baru masuk, belum diproses
                'timer_minutes' => 2, // Sudah menunggu 2 menit
                'items' => [
                    ['name' => 'Kopi Susu Gula Aren', 'quantity' => 2, 'note' => 'Less sugar'],
                    ['name' => 'Croissant', 'quantity' => 1, 'note' => 'Hangatin'],
                ]
            ],
            [
                'id' => 880,
                'table_number' => 'Meja 01',
                'status' => 'preparing', // Sedang dibuat
                'timer_minutes' => 10,
                'items' => [
                    ['name' => 'Nasi Goreng Spesial', 'quantity' => 1, 'note' => 'Pedas mampus'],
                ]
            ]
        ]
    ]);
});

// B. Update Status Pesanan (Preparing -> Ready)
Route::patch('/orders/{id}/status', function ($id) {
    return response()->json([
        'message' => "Order #$id status updated",
        'new_status' => 'ready' // Simulasi berubah jadi ready
    ]);
});


// ============================================================================
// 5. CASHIER DASHBOARD (Monitoring)
// ============================================================================

Route::get('/orders/cashier', function () {
    return response()->json([
        'data' => [
            // Pesanan Unpaid (Baru checkout, belum bayar di Midtrans)
            [
                'id' => 900,
                'table_number' => 'Meja 05',
                'total_amount' => 50000,
                'payment_status' => 'unpaid',
                'status' => 'pending',
                'created_at' => '12:05 PM'
            ],
            // Pesanan Paid (Sudah lunas)
            [
                'id' => 899,
                'table_number' => 'Meja 02',
                'total_amount' => 150000,
                'payment_status' => 'paid',
                'status' => 'completed',
                'created_at' => '11:45 AM'
            ]
        ]
    ]);
});


// ============================================================================
// 6. ADMIN DASHBOARD (Management)
// ============================================================================

// A. Stats Dashboard
Route::get('/admin/stats', function () {
    return response()->json([
        'today_revenue' => 2500000,
        'today_orders' => 45,
        'top_selling' => 'Kopi Susu Gula Aren'
    ]);
});

// B. Management Tables (Untuk Generate QR)
Route::get('/admin/tables', function () {
    return response()->json([
        'data' => [
            ['id' => 1, 'table_number' => 'Meja 01', 'qr_uuid' => 'uuid-m1-xya', 'qr_url' => 'http://app.com/?table=1'],
            ['id' => 2, 'table_number' => 'Meja 02', 'qr_uuid' => 'uuid-m2-xyb', 'qr_url' => 'http://app.com/?table=2'],
            ['id' => 3, 'table_number' => 'Meja 03', 'qr_uuid' => 'uuid-m3-xyc', 'qr_url' => 'http://app.com/?table=3'],
        ]
    ]);
});

// C. CRUD Products (Simulasi Sukses)
Route::post('/admin/products', function() {
    return response()->json(['message' => 'Product created successfully'], 201);
});
Route::delete('/admin/products/{id}', function($id) {
    return response()->json(['message' => "Product #$id deleted successfully"]);
});
