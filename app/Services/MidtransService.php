<?php

namespace App\Services;

use App\Models\Pesanan;
use Illuminate\Support\Facades\Log;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Transaction;

class MidtransService
{
    public function __construct()
    {
        $this->setupConfig();
    }

    private function setupConfig(): void
    {
        Config::$serverKey = config('midtrans.server_key') ?: env('MIDTRANS_SERVER_KEY');
        Config::$isProduction = config('midtrans.is_production') ?: env('MIDTRANS_IS_PRODUCTION', false);
        Config::$isSanitized = config('midtrans.is_sanitized') ?: env('MIDTRANS_SANITIZE', true);
        Config::$is3ds = config('midtrans.is_3ds') ?: env('MIDTRANS_3DS', true);
    }


    public function createSnapToken(Pesanan $pesanan, array $items, array $customerData): array
    {
        try {
            $customerDetails = [
                'first_name' => $customerData['first_name'] ?? $pesanan->nama,
            ];

            if (!empty($customerData['email']) && filter_var($customerData['email'], FILTER_VALIDATE_EMAIL)) {
                $customerDetails['email'] = $customerData['email'];
            }

            if (!empty($customerData['phone'])) {
                $customerDetails['phone'] = $customerData['phone'];
            }

            $params = [
                'transaction_details' => [
                    'order_id' => $pesanan->order_id,
                    'gross_amount' => (int) $pesanan->total,
                ],
                'item_details' => $this->formatItems($items),
                'customer_details' => $customerDetails,
                'expiry' => [
                    'unit' => 'minutes',
                    'duration' => 2,
                ],
            ];

            Log::info('Creating Snap Token', [
                'order_id' => $pesanan->order_id,
                'params' => $params
            ]);

            $snapToken = Snap::getSnapToken($params);

            Log::info('Snap Token Created', [
                'order_id' => $pesanan->order_id,
                'token' => $snapToken,
            ]);

            return [
                'token' => $snapToken,
            ];

        } catch (\Exception $e) {
            Log::error('Failed to create Snap Token', [
                'order_id' => $pesanan->order_id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    private function formatItems(array $items): array
    {
        return array_map(function ($item) {
            return [
                'id' => $item['idmenu'] ?? $item['id'],
                'price' => (int) ($item['harga'] ?? $item['price']),
                'quantity' => (int) ($item['jumlah'] ?? $item['quantity']),
                'name' => substr($item['nama_menu'] ?? $item['name'] ?? 'Item', 0, 50),
            ];
        }, $items);
    }


    public function handleNotification(array $notificationData): bool
    {
        try {
            Log::info('Midtrans Notification Received', $notificationData);

            $orderId = $notificationData['order_id'] ?? null;
            $transactionStatus = $notificationData['transaction_status'] ?? null;
            $paymentType = $notificationData['payment_type'] ?? null;
            $fraudStatus = $notificationData['fraud_status'] ?? null;

            if (!$orderId || !$transactionStatus) {
                Log::error('Invalid notification data', $notificationData);
                return false;
            }

            $pesanan = Pesanan::where('order_id', $orderId)->first();

            if (!$pesanan) {
                Log::error('Pesanan not found', ['order_id' => $orderId]);
                return false;
            }

            $statusBayar = $this->resolvePaymentStatus($transactionStatus, $fraudStatus);

            $pesanan->update([
                'status_bayar' => $statusBayar,
                'metode_bayar' => $paymentType,
            ]);

            Log::info('Pesanan updated from notification', [
                'order_id' => $orderId,
                'status_bayar' => $statusBayar,
                'metode_bayar' => $paymentType,
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Error handling notification', [
                'error' => $e->getMessage(),
                'data' => $notificationData
            ]);
            return false;
        }
    }


    private function resolvePaymentStatus(string $transactionStatus, ?string $fraudStatus): string
    {
        if ($transactionStatus === 'capture') {
            return $fraudStatus === 'accept' ? 'capture' : 'challenge';
        }

        if ($transactionStatus === 'settlement') {
            return 'settlement';
        }

        if (in_array($transactionStatus, ['deny', 'expire', 'cancel'])) {
            return $transactionStatus;
        }

        return $transactionStatus;
    }


    public function checkTransactionStatus(string $orderId): ?array
    {
        try {
            $status = Transaction::status($orderId);
            return (array) $status;
        } catch (\Exception $e) {
            Log::error('Failed to check transaction status', [
                'order_id' => $orderId,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }


    public static function generateOrderId(): string
    {
        return 'ORDER-' . date('Ymd') . '-' . strtoupper(uniqid());
    }


    public static function getSnapUrl(): string
    {
        $isProduction = env('MIDTRANS_IS_PRODUCTION', false);
        return $isProduction
            ? 'https://app.midtrans.com/snap/snap.js'
            : 'https://app.sandbox.midtrans.com/snap/snap.js';
    }


    public static function getClientKey(): string
    {
        return env('MIDTRANS_CLIENT_KEY', '');
    }
}
