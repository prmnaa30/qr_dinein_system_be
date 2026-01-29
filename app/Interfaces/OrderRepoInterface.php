<?php

namespace App\Interfaces;

interface OrderRepoInterface
{
    public function create(array $data);
    public function createItem(array $data);
    public function getById($id);
    public function getByStatus(array $statuses);
    public function getKitchenOrders();
    public function getCashierOrders(array $filters);
    public function getSalesSummary($date);
    public function getTopSellingItems($limit = 5);
    public function getOrdersByDateRange($startDate, $endDate);
    public function getOrderWithItems($id);
    public function getMonthlyStats();
    public function getActiveOrdersCount();
    public function getTotalItemsSoldToday();
    public function updateOrderStatus($id, string $status);
}
