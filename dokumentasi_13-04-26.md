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

## 2. Feature: Section Detail Pembayaran di Halaman Pesan

### Masalah
Popup Midtrans ditutup tapi user nggak ada feedback/petunjuk cara bayar.

### Solusi
Tambah **Section Detail Pembayaran** yang muncul setelah popup ditutup dengan 3 cara:

#### Cara 1: onPending Callback
```javascript
onPending: function(result) {
    // Auto-show section saat user pilih metode pembayaran
    showPaymentDetailSection();
    startStatusPolling();
}
```

#### Cara 2: onClose Callback
```javascript
onClose: function() {
    // Show section ketika popup ditutup
    showPaymentDetailSection();
    startStatusPolling();
}
```

#### Cara 3: Fallback Timer (5 detik)
```javascript
// Fallback: Auto show section setelah 5 detik (kalau onClose gagal)
setTimeout(() => {
    if (section.style.display === 'none') {
        showPaymentDetailSection();
        startStatusPolling();
    }
}, 5000);
```

### Fitur Section
- **Order ID** (dengan tombol copy)
- **Total Pembayaran**
- **QR Code Image** (jika tersedia)
- **QR Code URL** (bisa copy untuk QRIS)
- **Status realtime** dengan badge (PENDING/SETTLEMENT/EXPIRE)
- **Auto-polling** cek status setiap 10 detik
- **Tombol manual** "Ambil QR Code" kalau onPending gagal

### File Terubah
- `resources/views/pages/pelanggan/create-pesanan.blade.php`

---

## 3. Bug Fix: CSRF Token untuk Webhook

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

## 4. Feature: GET Route untuk Testing Webhook

### Masalah
Tidak ada cara cek apakah endpoint webhook aktif via browser.

### Solusi
Tambah route GET untuk testing:

```php
Route::get('midtrans/notification', function () {
    return response()->json([
        'status' => true,
        'message' => 'Webhook endpoint aktif. Gunakan POST method...',
        'endpoint' => url('/midtrans/notification'),
        'method' => 'POST',
    ]);
})->name('midtrans.notification.test');
```

### File Terubah
- `routes/web.php` (Baris 105-115)

---

## 5. Setup Ngrok untuk Webhook Testing

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

## 6. Alur Webhook Flow (Final)

```
[User bayar via QRIS/VA]
        ↓
[Midtrans Process Payment]
        ↓
[Midtrans kirim POST ke webhook]
        ↓
POST https://xxx.ngrok-free.app/midtrans/notification
Body: {
    "order_id": "ORDER-xxx",
    "transaction_status": "settlement",
    "payment_type": "qris"
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
[Frontend auto-detect via polling]
Status badge berubah hijau → Redirect ke riwayat transaksi
```

---

## 7. Status Pembayaran (Mapping)

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

## File yang Diubah Hari Ini

| File | Perubahan |
|------|-----------|
| `app/Services/MidtransService.php` | Refactor resolvePaymentStatus() method |
| `resources/views/pages/pelanggan/create-pesanan.blade.php` | Tambah section detail pembayaran + polling |
| `bootstrap/app.php` | CSRF exception untuk webhook |
| `routes/web.php` | GET route untuk testing webhook |
| `.env` | Update APP_URL ke ngrok |

---

## Next Steps / TODO

- [x] Fix status_bayar mapping (boolean → string)
- [x] Section detail pembayaran dengan QR Code
- [x] Webhook handler dengan CSRF fix
- [x] Ngrok setup untuk testing
- [x] Test webhook end-to-end
- [ ] Email notifikasi setelah pembayaran
- [ ] Export PDF invoice pesanan
- [ ] Fitur stok menu (track available quantity)
- [ ] Admin monitoring semua pesanan
- [ ] Production deployment (ganti ke Midtrans Production Key)

---

**Dokumentasi Update:** 13 April 2026  
**Status:** Webhook siap production, project ready for final testing 🚀
