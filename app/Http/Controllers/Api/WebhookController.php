<?php

namespace App\Http\Controllers\Api;

use App\Events\OrderPaid;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Transaction;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Midtrans\Notification;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\Authenticated;

#[Group('Webhook Management', 'APIs for handling payment gateway webhooks in the system.')]
class WebhookController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    #[Endpoint('Penanganan Webhook Midtrans', 'Menangani webhook dari layanan pembayaran Midtrans untuk memperbarui status pembayaran pesanan.')]
    #[Response(content: '{"message": "Webhook Response Sent"}', status: 200)]
    #[Response(content: '{"message": "Error"}', status: 500)]
    public function handleMidtrans(Request $request)
    {
        try {
            $midtransData = $request->all();

            $transactionStatus = $request->input('transaction_status');
            $paymentType = $request->input('payment_type');
            $orderIdRaw = $request->input('order_id');
            $amount = $request->input('gross_amount');
            $midtransId = $request->input('transaction_id');

            $orderId = explode('-', $orderIdRaw)[0];

            $order = Order::findOrFail($orderId);

            $paymentMethod = null;
            if (isset($midtransData['va_numbers'][0]['bank'])) {
                $paymentMethod = $midtransData['va_numbers'][0]['bank'];
            } elseif ($paymentType == 'qris') {
                $paymentMethod = 'qris';
            } else {
                $paymentMethod = $paymentType;
            }

            Transaction::updateOrCreate(
                ['midtrans_transaction_id' => $midtransId],
                [
                    'order_id' => $orderId,
                    'payment_type' => $paymentType,
                    'payment_method' => $paymentMethod,
                    'gross_amount' => $amount,
                    'status' => $transactionStatus,
                    'raw_response' => $midtransData
                ]
            );

            if ($transactionStatus == 'settlement') {
                $order->update([
                    'payment_status' => 'paid',
                    'status' => 'pending'
                ]);

                OrderPaid::dispatch($order);
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
