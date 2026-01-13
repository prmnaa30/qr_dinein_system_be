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
}
