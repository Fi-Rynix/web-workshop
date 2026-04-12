# Dokumentasi Project WebWorkshop - 12 April 2026

## Ringkasan Project
Implementasi **Role-Based System (3 Role)** + **Midtrans Payment Integration** untuk studi kasus e-commerce sederhana dengan fitur pemesanan tanpa login (Guest Mode).

---

## Struktur Role System

| Role | idrole | Folder Views | Redirect Login | Akses |
|------|--------|--------------|----------------|-------|
| **Admin** | 1 | `pages/` | `/dashboard` | Semua halaman existing (Kategori, Buku, Barang, PDF, Modul 4-5) |
| **Vendor** | 2 | `pages/vendor/` | `/vendor/dashboard` | Dashboard, Kelola Menu, Lihat Pesanan Masuk |
| **Pelanggan** | 3 | `pages/pelanggan/` | `/pelanggan/dashboard` | Dashboard, Pesan Menu, Riwayat Pesanan |

---

## Alur Guest User (Pesan Tanpa Login)

### Flow Baru (Final):
```
[User Guest] 
    ↓
[Buka /pesan] → Tidak ada session, tidak ada user login
    ↓
[Isi Form] → Pilih Vendor → Pilih Menu → Keranjang
    ↓
[Klik "Bayar Sekarang"]
    ↓
[Backend] Buat user Guest_0000001 (hanya untuk DB, tidak login)
    ↓
[Generate Snap Token + QR Code URL]
    ↓
[Popup Midtrans] → Pilih metode (QRIS/VA/Kartu)
    ↓
[Webhook Midtrans] → Update status bayar
    ↓
[Vendor] Lihat pesanan masuk
```

### Detail Implementasi Guest:
1. **User dibuat saat SUBMIT** (bukan saat buka halaman)
2. **Tidak ada session login** untuk guest
3. **User hanya disimpan ke DB** untuk relasi pesanan
4. **Format nama**: `Guest_0000001` (auto increment 7 digit)
5. **Email & password nullable/random**

---

## Struktur File Views (Final)

### Vendor Views:
```
pages/vendor/
├── dashboard.blade.php
├── index-menu.blade.php          # List menu + modal CRUD
├── create-menu.blade.php         # Modal create (el-dialog)
├── edit-menu.blade.php           # Modal edit (el-dialog)
├── delete-menu.blade.php         # Modal delete (el-dialog)
├── index-transaksi.blade.php     # List pesanan masuk
└── detail-transaksi.blade.php    # Modal detail pesanan
```

### Pelanggan Views:
```
pages/pelanggan/
├── dashboard.blade.php
├── create-pesanan.blade.php      # Form pemesanan (guest/login)
├── index-transaksi.blade.php     # Riwayat pesanan
└── detail-transaksi.blade.php    # Detail pesanan
```

### Perubahan Format Penamaan:
- ❌ ~~`pesanan/create.blade.php`~~ → ✅ `create-pesanan.blade.php`
- ❌ ~~`pesanan/index.blade.php`~~ → ✅ `index-transaksi.blade.php`
- ❌ ~~`pesanan/show.blade.php`~~ → ✅ `detail-transaksi.blade.php`

---

## Database Structure

### Tabel Pesanan (Final Schema):
```sql
CREATE TABLE pesanan (
    idpesanan INT AUTO_INCREMENT PRIMARY KEY,
    iduser INT NOT NULL,              -- FK ke user (guest atau login)
    order_id VARCHAR(50) UNIQUE,      -- Format: ORDER-YYYYMMDD-XXXXXXX
    nama VARCHAR(255),                -- Nama customer
    timestamp DATETIME,
    total INT,
    metode_bayar VARCHAR(50) NULL,    -- gopay, bank_transfer, qris, dll
    channel VARCHAR(50) NULL,           -- gopay, bni, bca, dll
    status_bayar VARCHAR(20) NULL DEFAULT 'pending',
    customer_email VARCHAR(255) NULL,
    FOREIGN KEY (iduser) REFERENCES user(iduser)
);
```

### Status Bayar (VARCHAR):
| Status | Arti | Warna Badge |
|--------|------|-------------|
| `pending` | Menunggu pembayaran | Kuning |
| `settlement` | Berhasil (Transfer/VA/QRIS) | Hijau |
| `capture` | Berhasil (Kartu Kredit) | Hijau |
| `deny` | Ditolak | Merah |
| `expire` | Kadaluarsa | Merah |
| `cancel` | Dibatalkan | Merah |

---

## Midtrans Integration

### MidtransService.php (Key Methods):

#### 1. createSnapToken() - Generate Token + QR URL
```php
public function createSnapToken(Pesanan $pesanan, array $items, array $customerData): array
{
    // Return: [
    //     'token' => 'xxx',
    //     'qr_code_url' => 'https://api.sandbox.midtrans.com/...'
    // ]
}
```

**Validasi Email:**
- Email hanya dikirim ke Midtrans jika valid (FILTER_VALIDATE_EMAIL)
- Email kosong diabaikan, tidak menyebabkan error 400

#### 2. getQrCodeUrl() - Ambil QR dari Transaction
```php
public function getQrCodeUrl(string $orderId): ?string
{
    // Cari action 'generate-qr-code' dari response Midtrans
    // Return URL atau null
}
```

#### 3. handleNotification() - Webhook Handler
```php
public function handleNotification(array $notificationData): bool
{
    // Update status_bayar langsung dari Midtrans (string)
    // Update metode_bayar, channel, total
}
```

### Konfigurasi Midtrans (.env):
```env
MIDTRANS_SERVER_KEY=SB-Mid-server-xxxxx
MIDTRANS_CLIENT_KEY=SB-Mid-client-xxxxx
MIDTRANS_IS_PRODUCTION=false
MIDTRANS_SANITIZE=true
MIDTRANS_3DS=true
```

### Snap Token Expiry:
- **Default**: 24 jam
- **Custom**: 2 menit (untuk testing cepat)
```php
'expiry' => [
    'unit' => 'minutes',
    'duration' => 2,
]
```

---

## Routes Structure

### Public Routes (Tanpa Middleware):
```php
// Halaman pemesanan (guest mode)
Route::get('pesan', [PesananController::class, 'createPublic']);
Route::post('pesan', [PesananController::class, 'storePublic']);

// API untuk load data
Route::get('api/get-vendors', [PesananController::class, 'getVendors']);
Route::get('api/get-menu-by-vendor', [PesananController::class, 'getMenuByVendor']);

// Webhook Midtrans
Route::post('midtrans/notification', [MidtransController::class, 'notification']);
```

### Protected Routes (Auth + Verif + Role):
```php
// Vendor (idrole = 2)
Route::middleware(['auth', 'check_verif', 'check.role:2'])->group(function () {
    Route::get('vendor/dashboard', [VendorController::class, 'index']);
    Route::get('vendor/menu', [MenuController::class, 'index']);
    Route::post('vendor/menu', [MenuController::class, 'store']);
    Route::put('vendor/menu/{id}', [MenuController::class, 'update']);
    Route::delete('vendor/menu/{id}', [MenuController::class, 'destroy']);
    Route::get('vendor/pesanan', [TransaksiController::class, 'index']);
});

// Pelanggan (idrole = 3)
Route::middleware(['auth', 'check_verif', 'check.role:3'])->group(function () {
    Route::get('pelanggan/dashboard', ...);
    Route::get('pelanggan/transaksi', [PesananController::class, 'index']);
    Route::get('pelanggan/transaksi/{id}', [PesananController::class, 'show']);
});
```

---

## Frontend Features (JavaScript)

### 1. Select Berjenjang (Vendor → Menu):
```javascript
// Pilih Vendor → Fetch API get-menu-by-vendor → Render Menu Cards
```

### 2. Keranjang (Cart):
- Add item dengan modal (jumlah + catatan)
- Edit jumlah langsung di keranjang
- Delete item dari keranjang
- Hitung total otomatis

### 3. Midtrans Snap.js Integration:
```javascript
snap.pay(snapToken, {
    onSuccess: function(result) { ... },
    onPending: function(result) { 
        // Tampilkan QR Code jika ada
    },
    onError: function(result) { ... },
    onClose: function() { ... }
});
```

### 4. QR Code Display:
- QR Code URL dari response backend
- Tampil di popup SweetAlert2
- Bisa scan langsung atau download

---

## Halaman Create-Pesanan (Dual Mode)

### Mode Guest (Tidak Login):
- Header: "Selamat datang, Guest"
- Tombol: "Login"
- Input Nama: Kosong (placeholder)
- Redirect sukses: `/login`

### Mode Login (Pelanggan):
- Header: "Selamat datang, [Nama User]"
- Tombol: "Riwayat Pesanan"
- Input Nama: Auto-fill `Auth::user()->nama`
- Redirect sukses: `/pelanggan/transaksi`

---

## File Upload (Menu Images)

### Folder Storage:
- **Path**: `public/images/menu/`
- **Format**: JPG, PNG, GIF
- **Max Size**: 2MB (di-enforce di frontend & backend)
- **Nama File**: `timestamp_namaoriginal.ext`

### Controller Logic:
```php
if ($request->hasFile('gambar')) {
    $file = $request->file('gambar');
    $filename = time() . '_' . $file->getClientOriginalName();
    $file->move(public_path('images/menu'), $filename);
    $pathGambar = 'images/menu/' . $filename;
}
```

---

## Testing & Debugging

### 1. Test Guest Mode:
```
1. Buka /pesan (tanpa login)
2. Pilih vendor → Pilih menu → Tambah keranjang
3. Isi nama → Klik Bayar
4. Check database: user Guest_0000001 auto-create
5. Check pesanan: iduser terisi dengan user guest
```

### 2. Test Midtrans Sandbox:
```
1. Generate pesanan
2. Di popup Midtrans, pilih QRIS atau Virtual Account
3. QR Code muncul di popup
4. Simulate payment via Midtrans Dashboard
   atau tunggu webhook (perlu ngrok untuk localhost)
```

### 3. Check Webhook (Tanpa Ngrok):
- Gunakan route simulate manual
- Atau update database langsung:
```sql
UPDATE pesanan SET status_bayar = 'settlement' WHERE order_id = 'ORDER-xxx';
```

---

## Ngrok Setup (Untuk Webhook):

### Command:
```bash
# Terminal 1
php artisan serve

# Terminal 2
ngrok http 8000
```

### Midtrans Dashboard Settings:
- **Payment Notification URL**: `https://[ngrok-url]/midtrans/notification`
- **Payment Redirect URL**: `https://[ngrok-url]/pelanggan/transaksi`

---

## Troubleshooting Notes

### 1. Error: "customer_details.email format is invalid"
**Solusi**: Email kosong diabaikan, hanya kirim jika valid (sudah fix di MidtransService)

### 2. Error: "Method getOrCreateGuestUser does not exist"
**Solusi**: Rename method ke `createGuestUser()` atau sebaliknya (sudah fix)

### 3. QR Code URL Null:
**Penyebab**: 
- User belum pilih metode pembayaran QRIS di popup
- Timing: QR generate setelah popup dibuka

**Solusi**: Tampilkan QR di `onPending` callback atau gunakan VA untuk test

### 4. CSS/Asset Broken di Ngrok:
**Solusi**: Tambahkan di `.env`:
```env
APP_URL=https://[ngrok-url].ngrok.io
ASSET_URL=https://[ngrok-url].ngrok.io
```

---

## Next Steps / TODO

### Prioritas 1 (Done ✅):
- [x] Role-based system (3 role)
- [x] Guest mode pemesanan
- [x] Midtrans integration (Snap.js)
- [x] QR Code display
- [x] Webhook handler
- [x] CRUD Menu Vendor
- [x] Transaksi views

### Prioritas 2 (Optional):
- [ ] Email notifikasi setelah pembayaran
- [ ] Export PDF invoice pesanan
- [ ] Fitur stok menu (track available quantity)
- [ ] Admin monitoring semua pesanan
- [ ] Analytics dashboard (grafik penjualan)

---

## Catatan Penting

1. **Guest User**: Hanya untuk DB tracking, tidak bisa login ulang
2. **Status Bayar**: String VARCHAR (bukan boolean), sesuai Midtrans response
3. **QR Code**: URL dari Midtrans API, bukan generate sendiri
4. **Upload File**: Semua di `public/images/menu/`, cek permission folder
5. **Email**: Validasi strict sebelum kirim ke Midtrans
6. **Expire**: 2 menit untuk testing, production bisa 24 jam

---

**Dokumentasi Update:** 12 April 2026
**Status**: Project siap testing & deploy ke production
