<?php

namespace App\Repositories;

use App\Interfaces\OrderRepoInterface;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;

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

    public function getSalesSummary($date)
    {
        return Order::whereDate('created_at', $date)
            ->where('payment_status', 'paid')
            ->select(
                DB::raw('COUNT(*) as total_transactions'),
                DB::raw('SUM(total_price) as total_revenue')
            )
            ->first();
    }

    public function getTopSellingItems($limit = 5)
    {
        return OrderItem::whereHas('order', function ($q) {
            $q->where('payment_status', 'paid');
        })
            ->select('product_id', DB::raw('SUM(quantity) as total_sold'))
            ->groupBy('product_id')
            ->with('product')
            ->orderByDesc('total_sold')
            ->take($limit)
            ->get();
    }

    public function getOrdersByDateRange($startDate, $endDate)
    {
        return Order::whereBetween('created_at', [
            $startDate . ' 00:00:00',
            $endDate . ' 23:59:59'
        ])
            ->where('payment_status', 'paid')
            ->with(['items.product', 'table', 'latestTransaction'])
            ->get();
    }

    public function getOrderWithItems($id)
    {
        return Order::with('items.product')->find($id);
    }

    public function getMonthlyStats()
    {
        return Order::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->where('payment_status', 'paid')
            ->select(
                DB::raw('COUNT(*) as total_transactions'),
                DB::raw('SUM(total_price) as total_revenue')
            )
            ->first();
    }

    public function getActiveOrdersCount()
    {
        return Order::whereIn('status', ['pending', 'preparing'])
            ->where('payment_status', 'paid')
            ->count();
    }

    public function getTotalItemsSoldToday()
    {
        return OrderItem::whereHas('order', function ($q) {
                $q->whereDate('created_at', now())
                  ->where('payment_status', 'paid');
            })
            ->sum('quantity');
    }

    public function updateOrderStatus($id, string $status)
    {
        $order = Order::findOrFail($id);
        $order->status = $status;
        $order->save();

        return $order;
    }
}
