<?php

namespace App\Services;

use App\Interfaces\OrderRepoInterface;
use Carbon\Carbon;

class DashboardService
{
    protected $orderRepository;

    /**
     * Create a new class instance.
     */
    public function __construct(OrderRepoInterface $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    public function getAdminDashboardData()
    {
        $today = Carbon::today();

        $salesToday = $this->orderRepository->getSalesSummary($today);

        $topItemsRaw = $this->orderRepository->getTopSellingItems(5);
        $topItems = $topItemsRaw->map(function ($item) {
            return [
                'product_name' => $item->product->name,
                'category' => $item->product->category->name ?? '-',
                'total_sold' => (int) $item->total_sold,
                'price_current' => $item->product->price,
            ];
        });

        return [
            'date' => $today->format('d M Y'),
            'stats' => [
                'revenue_today' => (int) $salesToday->total_revenue ?? 0,
                'transactions_today' => $salesToday->total_transaction ?? 0,
            ],
            'top_selling_items' => $topItems
        ];
    }

    public function getSalesReport($startDate, $endDate)
    {
        $orders = $this->orderRepository->getOrdersByDateRange($startDate, $endDate);

        return $orders->map(function ($order) {
            $itemsSummary = $order->items->map(function ($item) {
                $notes = $item->notes ? " ({$item->notes}" : "";
                return "{$item->quantity}x {$item->product->name}{$notes}";
            })->implode(', ');

            $trx = $order->latestTransaction;
            $method = $trx ? ($trx->payment_method ?? $trx->payment_type) : 'Manual/Cash';

            return [
                'order_id' => $order->id,
                'date' => $order->created_at->format('Y-m-d H:i'),
                'customer_name' => $order->customer_name,
                'table_number' => $order->table->table_number ?? "Takeaway",
                'items_summary' => $itemsSummary,
                'total_price' => (int) $order->total_price,
                'payment_method' => strtoupper($method)
            ];
        });
    }
}
