<?php

namespace App\Http\Controllers;

use App\Models\Pesanan;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MidtransController extends Controller
{
    protected $midtransService;

    public function __construct()
    {
        $this->midtransService = new MidtransService();
    }

    /**
     * Handle notification dari Midtrans (Webhook)
     */
    public function notification(Request $request)
    {
        try {
            $notificationData = $request->all();

            Log::info('Midtrans Webhook Received', $notificationData);

            // Validasi data
            if (empty($notificationData['order_id']) || empty($notificationData['transaction_status'])) {
                Log::error('Invalid webhook data', $notificationData);
                return response()->json(['status' => false, 'message' => 'Invalid data'], 400);
            }

            $orderId = $notificationData['order_id'];
            $transactionStatus = $notificationData['transaction_status'];
            $paymentType = $notificationData['payment_type'] ?? null;

            // Cari pesanan
            $pesanan = Pesanan::where('order_id', $orderId)->first();

            if (!$pesanan) {
                Log::error('Pesanan not found for webhook', ['order_id' => $orderId]);
                return response()->json(['status' => false, 'message' => 'Order not found'], 404);
            }

            // Update pesanan dengan data dari Midtrans
            $updateData = [
                'status_bayar' => $transactionStatus, // Simpan status string langsung (pending, settlement, deny, etc)
            ];

            // Update metode bayar dari payment_type selama payment_type valid
            if ($paymentType) {
                $updateData['metode_bayar'] = $paymentType; // payment_type: credit_card, qris, bank_transfer, dll
            }

            // Update total jika berbeda (dari amount Midtrans)
            if (isset($notificationData['gross_amount'])) {
                $grossAmount = (int) $notificationData['gross_amount'];
                if ($grossAmount != $pesanan->total) {
                    $updateData['total'] = $grossAmount;
                }
            }

            $pesanan->update($updateData);

            Log::info('Pesanan updated from webhook', [
                'order_id' => $orderId,
                'transaction_status' => $transactionStatus,
                'status_bayar' => $transactionStatus,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Notification processed',
            ]);

        } catch (\Exception $e) {
            Log::error('Error processing Midtrans webhook', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Internal server error',
            ], 500);
        }
    }

    /**
     * Manual check status endpoint
     */
    public function checkStatus($orderId)
    {
        $pesanan = Pesanan::where('order_id', $orderId)->first();

        if (!$pesanan) {
            return response()->json(['status' => false, 'message' => 'Order not found'], 404);
        }

        $status = $this->midtransService->checkTransactionStatus($orderId);

        if ($status) {
            // Update local status dengan string dari Midtrans
            $transactionStatus = $status['transaction_status'] ?? 'pending';

            $pesanan->update([
                'status_bayar' => $transactionStatus,
            ]);

            return response()->json([
                'status' => true,
                'midtrans_status' => $status,
                'local_status' => [
                    'status_bayar' => $transactionStatus,
                    'is_paid' => in_array($transactionStatus, ['settlement', 'capture']),
                    'order_id' => $orderId,
                ],
            ]);
        }

        return response()->json(['status' => false, 'message' => 'Failed to check status'], 500);
    }

    /**
     * Cek status webhook untuk order (digunakan oleh frontend polling)
     */
    public function webhookStatus($orderId)
    {
        $pesanan = Pesanan::where('order_id', $orderId)->first();

        if (!$pesanan) {
            return response()->json([
                'status' => false,
                'message' => 'Order not found'
            ], 404);
        }

        // Webhook dianggap diterima kalau status sudah settlement/capture (dari database)
        $webhookReceived = in_array($pesanan->status_bayar, ['settlement', 'capture']);

        return response()->json([
            'status' => true,
            'webhook_received' => $webhookReceived,
            'order_id' => $orderId,
            'idpesanan' => $pesanan->idpesanan,
            'status_bayar' => $pesanan->status_bayar,
            'metode_bayar' => $pesanan->metode_bayar,
            'total' => $pesanan->total,
        ]);
    }
}
