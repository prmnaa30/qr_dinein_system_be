<?php

namespace App\Services;

use App\Interfaces\OrderRepoInterface;
use App\Interfaces\ProductRepoInterface;
use Exception;
use Illuminate\Support\Facades\DB;

class OrderService
{
    protected $orderRepository;
    protected $productRepository;

    /**
     * Create a new class instance.
     */
    public function __construct(
        OrderRepoInterface $orderRepository,
        ProductRepoInterface $productRepository
    ) {
        $this->orderRepository = $orderRepository;
        $this->productRepository = $productRepository;
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
                    'notes' => $item['notes'] ?? null
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

            return $order;
        });
    }
}
