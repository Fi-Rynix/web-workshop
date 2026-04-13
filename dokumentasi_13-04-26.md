# Dokumentasi Project WebWorkshop - 13 April 2026

## Ringkasan Hari Ini
Fix bug, refactoring, dan setup Webhook Midtrans untuk production-ready payment system.

---

## 1. Bug Fix: MidtransService.php - Refactor resolvePaymentStatus()

### Masalah
Kolom `status_bayar` di database adalah **VARCHAR**, tapi kode menggunakan **boolean** (`true`/`false`) untuk menyimpan status.

```php
// SEBELUM (Bug):
$statusBayar = false;  // boolean
'status_bayar' => $statusBayar,  // jadi "1" atau "" di DB - SALAH!
```

### Solusi
Refactor dengan method baru `resolvePaymentStatus()` yang return **string** sesuai Midtrans response:

```php
// SESUDAH (Fix):
$statusBayar = $this->resolvePaymentStatus($transactionStatus, $fraudStatus);
'status_bayar' => $statusBayar,  // string: 'settlement', 'pending', dll

private function resolvePaymentStatus(string $transactionStatus, ?string $fraudStatus): string
{
    // Kartu kredit: cek fraud status
    if ($transactionStatus === 'capture') {
        return $fraudStatus === 'accept' ? 'capture' : 'challenge';
    }

    // Settlement: transfer/VA/QRIS berhasil
    if ($transactionStatus === 'settlement') {
        return 'settlement';
    }

    // Deny, expire, cancel: langsung return
    if (in_array($transactionStatus, ['deny', 'expire', 'cancel'])) {
        return $transactionStatus;
    }

    // Default: pending atau status lain
    return $transactionStatus;
}
```

### File Terubah
- `app/Services/MidtransService.php` (Baris 174-210)

---

## 2. Bug Fix: CSRF Token untuk Webhook

### Masalah
Midtrans webhook gagal dengan error:
```
419 Page Expired
```
Karena Laravel memerlukan CSRF token untuk POST request.

### Solusi
Tambahkan exception CSRF untuk route webhook di `bootstrap/app.php`:

```php
$middleware->validateCsrfTokens(except: [
    'midtrans/notification',
    '*/midtrans/notification',
]);
```

### File Terubah
- `bootstrap/app.php` (Baris 13-21)

---

## 3. Hapus getQrCodeUrl() dan QR Code URL dari Response

### Masalah
Kode `getQrCodeUrl()` mencoba mengambil QR Code URL dari Midtrans API setelah create snap token, tapi QR Code sebenarnya hanya tersedia setelah user pilih metode pembayaran QRIS di popup (via `actions` array di response `onPending`).

### Solusi
Hapus method `getQrCodeUrl()` dan hapus `qr_code_url` dari response `createSnapToken()`:

```php
// DIHAPUS - Method getQrCodeUrl() tidak lagi digunakan
// QR Code URL sekarang hanya diambil dari callback onPending Snap.js

public function createSnapToken(Pesanan $pesanan, array $items, array $customerData): array
{
    // ... kode ...
    return [
        'token' => $snapToken,
        // 'qr_code_url' => $qrCodeUrl,  // DIHAPUS
    ];
}
```

### File Terubah
- `app/Services/MidtransService.php` - Hapus method `getQrCodeUrl()` dan import `Transaction`
- `app/Http/Controllers/Pelanggan/PesananController.php` - Hapus referensi `qr_code_url` dari response
- `resources/views/pages/pelanggan/create-pesanan.blade.php` - Set `qr_code_url: null` saat inisialisasi

---

## 4. Guest User Tidak Login ke Session

### Masalah
Guest user yang baru dibuat dimasukkan ke session via `Auth::login()`, tapi seharusnya guest hanya untuk DB tracking saja.

### Solusi
Hapus `Auth::login()` dan `Auth::check()`:

```php
// SEBELUM:
if (!Auth::check()) {
    $user = $this->createGuestUser();
    Auth::login($user);  // DIHAPUS
}
$user = Auth::user();

// SESUDAH:
$user = $this->createGuestUser();  // Langsung create, tidak login
```

### File Terubah
- `app/Http/Controllers/Pelanggan/PesananController.php` - Method `storePublic()`

---

## 5. Section Status Pembayaran Baru (Dengan Polling Webhook)

### Masalah
Section detail pembayaran yang sebelumnya muncul saat `onClose` popup tidak reliable karena terhalang privacy policy Snap.js.

### Solusi
Buat section baru yang:
1. **Muncul langsung** setelah order dibuat (tidak tunggu onClose)
2. **Menampilkan tombol "Bayar Sekarang"** untuk buka Snap popup lagi
3. **Polling webhook status** tiap 3 detik untuk cek apakah data dari Midtrans sudah datang
4. **Auto-update UI** saat webhook diterima (metode bayar, status)

#### HTML Section (create-pesanan.blade.php):
```html
<div id="paymentStatusSection" class="row mt-4" style="display: none;">
    <div class="card border-info">
        <div class="card-header bg-info text-white">
            <h5>Status Pembayaran</h5>
            <span id="statusBadge" class="badge bg-warning text-dark">MENUNGGU</span>
        </div>
        <div class="card-body">
            <!-- Order ID, Total, Metode Bayar, Channel -->
            <!-- Tombol Buka Snap Lagi -->
            <!-- Loading Spinner (Menunggu webhook...) -->
        </div>
    </div>
</div>
```

#### JavaScript Functions:
```javascript
// Tampilkan section setelah order dibuat
function showPaymentStatusSection(orderData) {
    // Isi Order ID, Total
    // Tampilkan section
    // Scroll ke section
}

// Polling webhook status
function startWebhookPolling(orderId) {
    // Interval 3 detik
    // Cek endpoint /pesanan/{order_id}/webhook-status
}

// Cek status dari backend
function checkWebhookStatus(orderId) {
    // Fetch ke backend
    // Kalau webhook_received = true, update UI
}

// Update UI dengan data dari webhook
function updatePaymentStatusUI(data) {
    // Update badge dan metode bayar
    // Hentikan polling
    // Kalau settlement, redirect ke transaksi
}

// Buka popup Snap lagi
function reopenSnapPopup() {
    // snap.pay(currentOrder.snap_token, {...})
}
```

#### Backend Endpoint:
```php
// Route: GET /pesanan/{order_id}/webhook-status
public function webhookStatus($orderId)
{
    $pesanan = Pesanan::where('order_id', $orderId)->first();
    
    // Cek apakah webhook sudah datang (metode_bayar tidak null)
    $webhookReceived = !is_null($pesanan->metode_bayar);
    
    return response()->json([
        'webhook_received' => $webhookReceived,
        'status_bayar' => $pesanan->status_bayar,
        'metode_bayar' => $pesanan->metode_bayar,
    ]);
}
```

### File Terubah
- `resources/views/pages/pelanggan/create-pesanan.blade.php` - Tambah section HTML + JavaScript
- `app/Http/Controllers/MidtransController.php` - Tambah method `webhookStatus()`
- `routes/web.php` - Tambah route `GET /pesanan/{order_id}/webhook-status`

---

## 6. Debug Routes untuk Webhook

### Masalah
Sulit debug payload yang dikirim Midtrans karena webhook terjadi di background.

### Solusi
Tambah 3 route debug:

```php
// 1. Terima payload & simpan ke file
Route::post('midtrans/debug', function (Request $request) {
    $filename = 'midtrans_debug_' . date('Ymd_His') . '_' . uniqid() . '.json';
    $filepath = storage_path('logs/' . $filename);
    
    $data = [
        'received_at' => now()->toDateTimeString(),
        'ip_address' => $request->ip(),
        'headers' => $request->headers->all(),
        'body' => $request->all(),
    ];
    
    file_put_contents($filepath, json_encode($data, JSON_PRETTY_PRINT));
    
    return response()->json([
        'status' => true,
        'saved_to' => $filepath,
        'received_payload' => $request->all(),
    ]);
});

// 2. List semua file debug
Route::get('midtrans/debug/files', function () {
    $files = glob(storage_path('logs/midtrans_debug_*.json'));
    // Return list file dengan metadata
});

// 3. View isi file debug tertentu
Route::get('midtrans/debug/view/{filename}', function ($filename) {
    // Security check pattern
    // Return content file sebagai JSON
});
```

### Cara Pakai:
1. Ganti URL di Midtrans Dashboard ke `/midtrans/debug` (sementara)
2. Lakukan pembayaran
3. Cek `/midtrans/debug/files` untuk lihat daftar payload
4. Cek `/midtrans/debug/view/{filename}` untuk lihat detail

### File Terubah
- `routes/web.php` - Tambah 3 route debug

---

## 7. Setup Ngrok untuk Webhook Testing

### Konfigurasi
```bash
# Terminal 1 - Jalankan Laravel
php artisan serve

# Terminal 2 - Jalankan Ngrok
ngrok http 8000
```

### URL Ngrok
```
https://edgy-diabetes-liqueur.ngrok-free.dev -> http://localhost:8000
```

### Update .env
```env
APP_URL=https://edgy-diabetes-liqueur.ngrok-free.dev
```

### Midtrans Dashboard Settings
- **Payment Notification URL**: `https://edgy-diabetes-liqueur.ngrok-free.dev/midtrans/notification`
- **Payment Redirect URL**: `https://edgy-diabetes-liqueur.ngrok-free.dev/pelanggan/transaksi`

### Test Webhook via cURL
```bash
curl -X POST https://edgy-diabetes-liqueur.ngrok-free.dev/midtrans/notification \
  -H "Content-Type: application/json" \
  -d '{
    "order_id": "ORDER-20260412-69DBCE901E4A5",
    "transaction_status": "settlement",
    "payment_type": "qris",
    "gross_amount": 6000
  }'
```

**Response:**
```json
{"status":true,"message":"Notification processed"}
```

### Database Update
Setelah webhook dipanggil, status pesanan otomatis update:
```sql
-- SEBELUM:
status_bayar = 'pending', metode_bayar = NULL

-- SESUDAH:
status_bayar = 'settlement', metode_bayar = 'qris'
```

---

## 8. Alur Webhook Flow (Final)

```
[User klik Bayar]
        ↓
[Order dibuat] → Section Status Pembayaran muncul
                 (badge: MENUNGGU, tombol Bayar tersedia)
        ↓
[Snap popup terbuka] → User bisa bayar / tutup popup
        ↓
[User tutup popup] → Section tetap ada dengan tombol "Bayar Sekarang"
        ↓
[Polling berjalan] → Cek webhook status tiap 3 detik
        ↓
[User klik Bayar lagi] → Popup Snap terbuka lagi
        ↓
[User bayar via QRIS/VA] → Midtrans proses
        ↓
[Midtrans kirim POST ke webhook]
        ↓
POST https://xxx.ngrok-free.app/midtrans/notification
Body: {
    "order_id": "ORDER-xxx",
    "transaction_status": "settlement",
    "payment_type": "qris",        → metode_bayar
}
        ↓
[MidtransController::notification()]
        ↓
[Pesanan::update()]
status_bayar = 'settlement'
metode_bayar = 'qris'
        ↓
[Response ke Midtrans]
{"status":true,"message":"Notification processed"}
        ↓
[Frontend polling detect webhook_received = true]
        ↓
[Update UI Section]
- Badge: MENUNGGU → SETTLEMENT (hijau)
- Metode Bayar: - → QRIS
- Tombol Bayar: disembunyikan
        ↓
[Swal.fire success] → Redirect ke riwayat transaksi
```

---

## 9. Status Pembayaran (Mapping)

| Midtrans Status | DB status_bayar | Badge Warna | Keterangan |
|-----------------|-----------------|-------------|------------|
| `pending` | `pending` | 🟡 Kuning | Menunggu pembayaran |
| `settlement` | `settlement` | 🟢 Hijau | Transfer/VA/QRIS berhasil |
| `capture` | `capture` | 🟢 Hijau | Kartu kredit berhasil |
| `deny` | `deny` | 🔴 Merah | Ditolak |
| `expire` | `expire` | 🔴 Merah | Kadaluarsa |
| `cancel` | `cancel` | 🔴 Merah | Dibatalkan |
| `challenge` | `challenge` | 🟠 Oranye | Fraud review |

---

## 10. Perubahan Database Schema (Pesanan)

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `idpesanan` | INT (PK) | Auto increment |
| `iduser` | INT (FK) | Guest user atau user login |
| `order_id` | VARCHAR(50) | Format: ORDER-YYYYMMDD-XXXXXXX |
| `nama` | VARCHAR(255) | Nama customer |
| `timestamp` | DATETIME | Waktu pesanan dibuat |
| `total` | INT | Total pembayaran |
| `metode_bayar` | VARCHAR(50) | Isi dari webhook: qris, bank_transfer, dll |
| `status_bayar` | VARCHAR(20) | pending, settlement, capture, deny, expire, cancel |
| `customer_email` | VARCHAR(255) | Email customer (nullable) |

---

## File yang Diubah Hari Ini

| File | Perubahan |
|------|-----------|
| `app/Services/MidtransService.php` | Hapus getQrCodeUrl(), refactor resolvePaymentStatus() |
| `app/Http/Controllers/Pelanggan/PesananController.php` | Guest user tidak login, set default metode_bayar = 'Midtrans' |
| `app/Http/Controllers/MidtransController.php` | Tambah webhookStatus() method |
| `resources/views/pages/pelanggan/create-pesanan.blade.php` | Section Status Pembayaran baru dengan polling |
| `routes/web.php` | Tambah route webhook-status dan debug routes |
| `bootstrap/app.php` | CSRF exception untuk webhook |
| `.env` | Update APP_URL ke ngrok |

---

## Next Steps / TODO

- [x] Fix status_bayar mapping (boolean → string)
- [x] Hapus getQrCodeUrl() yang tidak reliable
- [x] Guest user tidak masuk session
- [x] Section Status Pembayaran dengan polling webhook
- [x] Tombol "Bayar Sekarang" untuk buka Snap lagi
- [x] Webhook handler dengan CSRF fix
- [x] Debug routes untuk logging payload
- [x] Ngrok setup untuk testing
- [ ] Email notifikasi setelah pembayaran
- [ ] Export PDF invoice pesanan
- [ ] Fitur stok menu (track available quantity)
- [ ] Admin monitoring semua pesanan
- [ ] Production deployment (ganti ke Midtrans Production Key)

---

**Dokumentasi Update:** 13 April 2026  
**Status:** Section status pembayaran dengan polling webhook siap testing 🚀
