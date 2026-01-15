<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Midtrans\Notification;

class WebhookController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function handleMidtrans(Request $request)
    {
        try {

            // TODO: remove comments on production
            // $notification = new Notification();
            //
            // $transactionStatus = $notification->transaction_status;
            // $type = $notification->payment_type;
            // $orderIdRaw = $notification->order_id;
            // $fraudStatus = $notification->fraud_status;
            $transactionStatus = $request->input('transaction_status');
            $type = $request->input('payment_type');
            $orderIdRaw = $request->input('order_id');
            $fraudStatus = $request->input('fraud_status');

            echo $transactionStatus;

            $orderId = explode('-', $orderIdRaw)[0];

            $order = Order::findOrFail($orderId);

            if ($transactionStatus == 'capture') {
                if ($type == 'credit_card') {
                    if ($fraudStatus == 'challenge') {
                        $order->update(['payment_status' => 'pending']);
                    } else {
                        $order->update(['payment_status' => 'paid']);
                    }
                }
            } elseif ($transactionStatus == 'settlement') {
                $order->update([
                    'payment_status' => 'paid',
                    'status' => 'pending'
                ]);

            } elseif ($transactionStatus == 'pending') {
                $order->update(['payment_status' => 'pending']);
            } elseif ($transactionStatus == 'deny') {
                $order->update(['payment_status' => 'failed']);
            } elseif ($transactionStatus == 'expire') {
                $order->update(['payment_status' => 'expired']);
            } elseif ($transactionStatus == 'cancel') {
                $order->update(['payment_status' => 'failed']);
            }

            return response()->json([
                'message' => 'Webhook Response Sent'
            ]);
        } catch (\Exception $e) {
            Log::error('Midtrans Webhook Error: ' . $e->getMessage());
            return response()->json(['message' => 'Error'], 500);
        }
    }
}
