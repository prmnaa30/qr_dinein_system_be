<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderStatusRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
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
    #[Response(content: '{"id": 1,"table_number": "A1","total_price": 50000,"payment_status": "unpaid","status": "pending","created_at": "2023-01-01T00:00.000Z","updated_at": "2023-01-01T0:00.000000Z"}', status: 200)]
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

    #[Endpoint('Daftar Pesanan Kitchen', 'Menampilkan daftar semua pesanan dengan payment_status = "paid"')]
    #[Authenticated]
    #[Response(content: '[{"id": 1,"table_number": "A1","total_price": 50000,"payment_status": "paid","status": "pending","created_at": "2023-01-01T00:00.000Z","updated_at": "2023-01-01T0:00.000000Z"}]', status: 200)]
    public function getKitchenOrders()
    {
        Gate::authorize('viewKitchen', Order::class);

        $orders = $this->orderService->getOrdersForKitchen();

        return OrderResource::collection($orders);
    }

    #[Endpoint('Daftar Pesanan Cashier', 'Menampilkan daftar semua pesanan di dashboard cashier')]
    #[Authenticated]
    #[Response(content: '[{"id": 1,"table_number": "A1","total_price": 50000,"payment_status": "paid","status": "pending","created_at": "2023-01-01T00:00.000Z","updated_at": "2023-01-01T0:00.000000Z"}]', status: 200)]
    public function getCashierOrders(Request $request)
    {
        Gate::authorize('viewCashier', Order::class);

        $orders = $this->orderService->getOrdersForCashier($request->all());

        return OrderResource::collection($orders);
    }

    #[Endpoint('Perbarui Status Pesanan', 'Memperbarui status pesanan berdasarkan ID.')]
    #[Authenticated]
    #[Response(content: '{"id": 1,"table_number": "A1","total_price": 50000,"payment_status": "paid","status": "completed","created_at": "2023-01-01T00:00.000Z","updated_at": "2023-01-01T0:00.000000Z"}', status: 200)]
    public function updateStatus(UpdateOrderStatusRequest $request, $id)
    {
        $order = Order::findOrFail($id);
        Gate::authorize('update', $order);

        $updatedOrder = $this->orderService->updateOrderStatus($id, $request->status);

        return new OrderResource($updatedOrder);
    }

    #[Endpoint('Tracking Status Pesanan', 'Melihat status pesanan setelah pembayaran melalui Midtrans')]
    #[Response('')]
    public function trackOrder(Request $request, $id)
    {
        $order = Order::with('items.product')->find($id);

        if (!$order) {
            return response()->json(['message' => 'Order tidak ditemukan!'], 404);
        }

        if ($request->has('table_id') && $order->table_id != $request->table_id) {
            return response()->json(['message' => 'Akses ditolak. Nomor meja tidak sesuai.'], 403);
        }

        $step = 0;
        $description = '';

        switch ($order->status) {
            case 'pending':
                $step = 1;
                $description = 'Pesanan menunggu konfirmasi dapur';
                break;
            case 'preparing':
                $step = 2;
                $description = 'Barista sedang meracik pesananmu';
                break;
            case 'ready':
                $step = 3;
                $description = 'Pesanan siap! Akan segera diantar ke mejamu';
                break;
            case 'completed':
                $step = 4;
                $description = 'Pesanan selesai. Selamat menikmati!';
                break;
            case 'cancelled':
                $step = 0;
                $description = 'Yah, pesanan dibatalkan.';
                break;
            default:
                $step = 1;
                $description = 'Menunggu pembayaran';
        }

        if ($order->payment_status === 'unpaid') {
            $step = 0;
            $description = 'Menunggu pembayaran diselesaikan';
        }

        return response()->json([
            'data' => [
                'order_id' => $order->id,
                'customer_name' => $order->customer_name,
                'table_number' => $order->table->table_number ?? '-',
                'status' => $order->status,
                'payment_status' => $order->payment_status,
                'ui_step' => $step,
                'ui_description' => $description,
                'items' => $order->items->map(function ($item) {
                    return [
                        'name' => $item->product->name,
                        'qty' => $item->quantity
                    ];
                })
            ]
        ]);
    }
}
