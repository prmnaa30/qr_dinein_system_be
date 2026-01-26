<?php

namespace App\Services;

use App\Events\OrderStatusUpdated;
use App\Interfaces\OrderRepoInterface;
use App\Interfaces\ProductRepoInterface;
use Exception;
use Illuminate\Support\Facades\DB;

class OrderService
{
    protected $orderRepository;
    protected $productRepository;
    protected $midtransService;

    /**
     * Create a new class instance.
     */
    public function __construct(
        OrderRepoInterface $orderRepository,
        ProductRepoInterface $productRepository,
        MidtransService $midtransService,
    ) {
        $this->orderRepository = $orderRepository;
        $this->productRepository = $productRepository;
        $this->midtransService = $midtransService;
    }

    public function createOrder(array $data)
    {
        return DB::transaction(function () use ($data) {
            $totalPrice = 0;
            $orderItemsData = [];

            foreach ($data['items'] as $item) {
                $product = $this->productRepository->getById($item['product_id']);

                if (!$product->is_available) {
                    throw new Exception("Product {$product} is not available right now...");
                }

                $subtotal = $product->price * $item['quantity'];
                $totalPrice += $subtotal;

                $orderItemsData[] = [
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price' => $product->price,
                    'notes' => $item['notes'] ?? null,
                ];
            }

            $order = $this->orderRepository->create([
                'table_id' => $data['table_id'],
                'customer_name' => $data['customer_name'],
                'total_price' => $totalPrice,
                'payment_status' => 'unpaid',
                'status' => 'pending'
            ]);

            foreach ($orderItemsData as $itemData) {
                $itemData['order_id'] = $order->id;
                $this->orderRepository->createItem($itemData);
            }

            $order->load('items.product');

            $snapToken = $this->midtransService->getSnapToken($order);

            $order->snap_token = $snapToken;
            $order->save();

            return $order;
        });
    }

    public function getOrdersForKitchen()
    {
        return $this->orderRepository->getKitchenOrders();
    }

    public function getOrdersForCashier(array $filters)
    {
        return $this->orderRepository->getCashierOrders($filters);
    }

    public function updateOrderStatus($id, string $newStatus)
    {
        $order = $this->orderRepository->updateOrderStatus($id, $newStatus);

        $order->load(['items.product', 'table']);

        OrderStatusUpdated::dispatch($order);

        return $order;
    }

    public function getOrderForTracking($id)
    {
        return $this->orderRepository->getOrderWithItems($id);
    }

    public function validateTableAccess($order, $tableId = null)
    {
        if ($tableId && $order->table_id != $tableId) {
            return false;
        }

        return true;
    }

    public function getOrderTrackingInfo($order)
    {
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

        return [
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
        ];
    }
}
