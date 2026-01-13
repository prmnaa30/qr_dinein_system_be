<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\Authenticated;

#[Group('Order Management', 'APIs for managing orders in the system.')]
class OrderController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    // #[Endpoint('Daftar Pesanan', 'Menampilkan daftar semua pesanan yang tersedia.')]
    // #[Authenticated]
    // #[Response(content: '[{"id": 1,"table_number": "A1","total_price": 50000,"status": "completed","created_at": "2023-01-01T00:00.000Z","updated_at": "2023-01-01T00:00.000000Z"}]', status: 200)]
    public function index()
    {
        // optional
    }

    #[Endpoint('Buat Pesanan Baru', 'Membuat pesanan baru dengan data yang diberikan.')]
    #[Authenticated]
    #[Response(content: '{"id": 1,"table_number": "A1","total_price": 50000,"status": "pending","created_at": "2023-01-01T00:00.000Z","updated_at": "2023-01-01T0:00.000000Z"}', status: 200)]
    #[Response(content: '{"message": "Error message"}', status: 400)]
    public function store(StoreOrderRequest $request)
    {
        try {
            $order = $this->orderService->createOrder($request->validated());

            $order->load(['items.product', 'table']);

            return new OrderResource($order);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    // #[Endpoint('Tampilkan Pesanan', 'Menampilkan detail pesanan berdasarkan ID.')]
    // #[Authenticated]
    // #[Response(content: '{"id": 1,"table_number": "A1","total_price": 50000,"status": "pending","created_at": "2023-01-01T0:00.000Z","updated_at": "2023-01-01T00:00.000000Z"}', status: 200)]
    public function show(string $id)
    {
        // optional
    }

    // #[Endpoint('Perbarui Pesanan', 'Memperbarui data pesanan berdasarkan ID.')]
    // #[Authenticated]
    // #[Response(content: '{"id": 1,"table_number": "A1","total_price": 50000,"status": "completed","created_at": "2023-01-01T00:00.000Z","updated_at": "2023-01-01T00:00.00000Z"}', status: 200)]
    public function update(Request $request, string $id)
    {
        // optional
    }

    // #[Endpoint('Hapus Pesanan', 'Menghapus pesanan berdasarkan ID.')]
    // #[Authenticated]
    // #[Response(content: '{"message": "Pesanan berhasil dihapus"}', status: 200)]
    public function destroy(string $id)
    {
        // optional
    }
}
