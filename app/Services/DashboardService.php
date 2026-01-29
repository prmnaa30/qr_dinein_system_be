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

        $salesMonth = $this->orderRepository->getMonthlyStats();

        $activeOrders = $this->orderRepository->getActiveOrdersCount();
        $itemsSoldToday = $this->orderRepository->getTotalItemsSoldToday();

        $revenueToday = $salesToday->total_revenue ?? 0;
        $trxToday = $salesToday->total_transactions ?? 0;
        $aov = $trxToday > 0 ? round($revenueToday / $trxToday) : 0;

        $topItemsRaw = $this->orderRepository->getTopSellingItems(5);
        $topItems = $topItemsRaw->map(function ($item) {
            return [
                'product_name' => $item->product->name,
                'category' => $item->product->category->name ?? '-',
                'total_sold' => (int) $item->total_sold,
                'price_current' => $item->product->price,
                'revenue_contribution' => $item->total_sold * $item->product->price
            ];
        });

        return [
            'meta' => [
                'date' => $today->format('d M Y'),
                'last_updated' => now()->format('H:i'),
            ],
            'cards' => [
                // Card 1: Omzet Hari Ini
                'revenue_today' => [
                    'label' => 'Today\'s Revenue',
                    'value' => (int) $revenueToday,
                    'prefix' => 'Rp',
                ],
                // Card 2: Total Transaksi Hari Ini
                'transactions_today' => [
                    'label' => 'Transactions',
                    'value' => (int) $trxToday,
                    'unit' => 'Orders',
                ],
                // Card 3: Total Item Terjual
                'items_sold_today' => [
                    'label' => 'Items Sold',
                    'value' => (int) $itemsSoldToday,
                    'unit' => 'Pcs',
                ],
                // Card 4: Omzet Bulan Ini
                'revenue_month' => [
                    'label' => 'This Month',
                    'value' => (int) ($salesMonth->total_revenue ?? 0),
                    'prefix' => 'Rp',
                ],
                // Card 5: Rata-rata belanja per orang
                'average_order_value' => [
                    'label' => 'Avg. Order Value',
                    'value' => $aov,
                    'prefix' => 'Rp',
                    'tooltip' => 'Rata-rata nominal per transaksi hari ini'
                ],
                // Card 6: Active Orders
                'kitchen_load' => [
                    'label' => 'Active Orders',
                    'value' => $activeOrders,
                    'unit' => 'Queue',
                    'status' => $activeOrders > 10 ? 'High' : 'Normal'
                ],
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
