<?php

namespace App\Repositories;

use App\Interfaces\OrderRepoInterface;
use App\Models\Order;
use App\Models\OrderItem;

class OrderRepository implements OrderRepoInterface
{
    public function create(array $data)
    {
        return Order::create($data);
    }

    public function createItem(array $data)
    {
        return OrderItem::create($data);
    }

    public function getById($id)
    {
        return Order::with(['items.product', 'table'])->findOrFail($id);
    }

    public function getByStatus(array $statuses)
    {
        return Order::with(['items.product', 'table'])
            ->whereIn('status', $statuses)
            ->latest()
            ->get();
    }

    public function getKitchenOrders()
    {
        return Order::with(['items.product', 'table'])
            ->where('payment_status', 'paid')
            ->whereIn('status', ['preparing', 'pending'])
            ->orderBy('created_at', 'asc')
            ->get();
    }

    public function getCashierOrders(array $filters)
    {
        $query = Order::with(['items.product', 'table']);

        if (isset($filters['payment_status'])) {
            $query->where('payment_status', $filters['payment_status']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->latest()->paginate(10);
    }

    public function updateOrderStatus($id, string $status)
    {
        $order = Order::findOrFail($id);
        $order->status = $status;
        $order->save();

        return $order;
    }
}
