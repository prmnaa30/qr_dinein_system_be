<?php

namespace App\Services;

use Midtrans\Config;
use Midtrans\Snap;

class MidtransService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = config('services.midtrans.is_production');
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    public function getSnapToken($order)
    {
        $params = [
            'transaction_details' => [
                'order_id' => $order->id . '-' . time(),
                'gross_amount' => $order->total_price
            ],

            'customer_details' => [
                'first_name' => $order->customer_name,
            ],

            'item_details' => $order->items->map(function ($item) {
                return [
                    'id' => $item->product_id,
                    'price' => $item->price,
                    'quantity' => $item->quantity,
                    'name' => substr($item->product->name, 0, 50),
                ];
            })->toArray()
        ];

        return Snap::getSnapToken($params);
    }
}
