<?php

namespace App\Services;

use App\Models\Pesanan;
use Illuminate\Support\Facades\Log;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Transaction;

class MidtransService
{
    /**
     * Constructor - Setup Midtrans configuration
     */
    public function __construct()
    {
        $this->setupConfig();
    }

    /**
     * Setup Midtrans configuration
     */
    private function setupConfig(): void
    {
        Config::$serverKey = config('midtrans.server_key') ?: env('MIDTRANS_SERVER_KEY');
        Config::$isProduction = config('midtrans.is_production') ?: env('MIDTRANS_IS_PRODUCTION', false);
        Config::$isSanitized = config('midtrans.is_sanitized') ?: env('MIDTRANS_SANITIZE', true);
        Config::$is3ds = config('midtrans.is_3ds') ?: env('MIDTRANS_3DS', true);
    }

    /**
     * Create Snap Token untuk transaksi
     *
     * @param Pesanan $pesanan
     * @param array $items Array of items (dari detail_pesanan)
     * @param array $customerData ['first_name', 'email', 'phone']
     * @return array ['token' => string, 'qr_code_url' => string|null]
     * @throws \Exception
     */
    public function createSnapToken(Pesanan $pesanan, array $items, array $customerData): array
    {
        try {
            // Build customer details
            $customerDetails = [
                'first_name' => $customerData['first_name'] ?? $pesanan->nama,
            ];

            // Only add email if provided and valid
            if (!empty($customerData['email']) && filter_var($customerData['email'], FILTER_VALIDATE_EMAIL)) {
                $customerDetails['email'] = $customerData['email'];
            }

            // Only add phone if provided
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

            // Dapatkan QR Code URL untuk QRIS
            $qrCodeUrl = $this->getQrCodeUrl($pesanan->order_id);

            Log::info('Snap Token Created', [
                'order_id' => $pesanan->order_id,
                'token' => $snapToken,
                'qr_url' => $qrCodeUrl,
            ]);

            return [
                'token' => $snapToken,
                'qr_code_url' => $qrCodeUrl,
            ];

        } catch (\Exception $e) {
            Log::error('Failed to create Snap Token', [
                'order_id' => $pesanan->order_id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get QR Code URL dari transaction status
     *
     * @param string $orderId
     * @return string|null
     */
    public function getQrCodeUrl(string $orderId): ?string
    {
        try {
            $status = Transaction::status($orderId);

            // Cek actions array dari response Midtrans
            if (isset($status->actions) && is_array($status->actions)) {
                foreach ($status->actions as $action) {
                    if ($action->name === 'generate-qr-code') {
                        Log::info('QR Code URL found', [
                            'order_id' => $orderId,
                            'qr_url' => $action->url
                        ]);
                        return $action->url ?? null;
                    }
                }
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Failed to get QR Code URL', [
                'order_id' => $orderId,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Format items untuk Midtrans
     */
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

    /**
     * Handle notification dari Midtrans (Webhook)
     *
     * @param array $notificationData Data dari Midtrans
     * @return bool
     */
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

            // Cari pesanan
            $pesanan = Pesanan::where('order_id', $orderId)->first();

            if (!$pesanan) {
                Log::error('Pesanan not found', ['order_id' => $orderId]);
                return false;
            }

            // Tentukan status bayar berdasarkan transaction_status dari Midtrans
            $statusBayar = $this->resolvePaymentStatus($transactionStatus, $fraudStatus);

            // Update pesanan
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

    /**
     * Resolve payment status dari Midtrans transaction_status
     *
     * @param string $transactionStatus Status dari Midtrans
     * @param string|null $fraudStatus Fraud status (untuk kartu kredit)
     * @return string Status bayar yang disimpan ke DB
     */
    private function resolvePaymentStatus(string $transactionStatus, ?string $fraudStatus): string
    {
        // Kartu kredit: cek fraud status dulu
        if ($transactionStatus === 'capture') {
            return $fraudStatus === 'accept' ? 'capture' : 'challenge';
        }

        // Status berhasil: settlement (transfer/VA/QRIS)
        if ($transactionStatus === 'settlement') {
            return 'settlement';
        }

        // Status gagal/expired
        if (in_array($transactionStatus, ['deny', 'expire', 'cancel'])) {
            return $transactionStatus;
        }

        // Default: pending atau status lain dari Midtrans
        return $transactionStatus;
    }

    /**
     * Cek status transaksi di Midtrans
     *
     * @param string $orderId
     * @return array|null
     */
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

    /**
     * Generate unique order ID
     *
     * @return string
     */
    public static function generateOrderId(): string
    {
        return 'ORDER-' . date('Ymd') . '-' . strtoupper(uniqid());
    }

    /**
     * Get Snap.js URL berdasarkan environment
     *
     * @return string
     */
    public static function getSnapUrl(): string
    {
        $isProduction = env('MIDTRANS_IS_PRODUCTION', false);
        return $isProduction
            ? 'https://app.midtrans.com/snap/snap.js'
            : 'https://app.sandbox.midtrans.com/snap/snap.js';
    }

    /**
     * Get Client Key
     *
     * @return string
     */
    public static function getClientKey(): string
    {
        return env('MIDTRANS_CLIENT_KEY', '');
    }
}
